<?php

namespace ZF\Doctrine\Audit\Tools\Generator\Trigger;

use Exception;
use Doctrine\Common\Persistence\ObjectManager;
use ZF\Doctrine\Audit\Persistence;
use ZF\Doctrine\Audit\Tools\Generator\GeneratorInterface;

final class MySQL implements
    Persistence\AuditObjectManagerAwareInterface,
    Persistence\ObjectManagerAwareInterface,
    GeneratorInterface
{
    use Persistence\AuditObjectManagerAwareTrait;
    use Persistence\ObjectManagerAwareTrait;

    private $config;

    public function __construct(ObjectManager $objectManager, ObjectManager $auditObjectManager, array $config)
    {
        $this->setObjectManager($objectManager);
        $this->setAuditObjectManager($auditObjectManager);
        $this->config = $config;

        // @codeCoverageIgnoreStart
        if ($this->getObjectManager()->getConnection()->getDatabasePlatform()->getName() !== 'mysql') {
            throw new Exception('Invalid database platform for MySQL trigger generator');
        }
        // @codeCoverageIgnoreEnd
    }

    public function generate()
    {
        /**
         * These two functions are used to group changes into revisions.
         */
        $sql = <<<EOF

DELIMITER //

DROP FUNCTION IF EXISTS close_revision_audit;//
CREATE FUNCTION close_revision_audit(
    p_userId BIGINT,
    p_userName varchar(255),
    p_userEmail varchar(255),
    p_comment text
) RETURNS boolean
BEGIN

    UPDATE audit.Revision_Audit SET
        connectionId = null,
        userId = p_userId,
        userName = p_userName,
        userEmail = p_userEmail,
        comment = p_comment
    WHERE connectionId = CONNECTION_ID();

    RETURN true;
END;//

DROP FUNCTION IF EXISTS get_revision_entity_audit;//
CREATE FUNCTION get_revision_entity_audit(
    p_targetEntity varchar(255) charset utf8 collate utf8_unicode_ci,
    p_revisionType varchar(255) charset utf8 collate utf8_unicode_ci
)
    RETURNS bigint
    READS SQL DATA
BEGIN

    DECLARE revisionTypeId BIGINT DEFAULT 0;
    DECLARE revisionId BIGINT DEFAULT 0;
    DECLARE targetEntityId BIGINT DEFAULT 0;

    SELECT id INTO revisionTypeId
    FROM audit.RevisionType_Audit
    WHERE name = p_revisionType
    LIMIT 1;

    SELECT id INTO revisionId
    FROM audit.Revision_Audit
    WHERE connectionId = CONNECTION_ID()
    LIMIT 1;

    IF revisionId = 0 THEN
        INSERT INTO audit.Revision_Audit (
            createdAt,
            connectionId
        ) VALUES (
            now(), CONNECTION_ID()
        );

        SET revisionId = LAST_INSERT_ID();
    END IF;

    SELECT id INTO targetEntityId
    FROM audit.TargetEntity_Audit
    WHERE name = p_targetEntity;

    INSERT INTO audit.RevisionEntity_Audit (
        revision_id,
        target_entity_id,
        revision_type_id
    ) VALUES (
        revisionId,
        targetEntityId,
        revisionTypeId
    );

    RETURN LAST_INSERT_ID();
END;//

EOF;

        // Now iterate through join entities and build trigger code for each.
        foreach ($this->config['joinEntities'] as $className => $config) {
            $auditClassName = $this->getAuditObjectManager()
                ->getRepository('ZF\Doctrine\Audit\Entity\AuditEntity')
                ->generateClassName($className);

            $metadataFactory = $this->getObjectManager()->getMetadataFactory();
            $metadata = $metadataFactory->getMetadataFor($config['ownerEntity']);

            foreach ($metadata->getAssociationMappings() as $mapping) {
                if (isset($mapping['joinTable'])) {
                    if ($mapping['joinTable']['name'] == $config['tableName']) {
                        $foundJoinEntity = true;
                        break;
                    }
                }
            }

            if (! $foundJoinEntity) {
                throw new Exception(
                    'joinTable '
                    . $targetClassName
                    . ' not found by tableName '
                    . $config['tableName']
                    . ' on ownerEntity: '
                    . $config['ownerEntity']
                );
            }

            $fields = [];
            foreach ($mapping['joinTable']['joinColumns'] as $column) {
                $column['dataType'] = $this->getObjectManager()
                    ->getClassMetadata($mapping['sourceEntity'])
                    ->getTypeOfField($column['referencedColumnName']);
                $fields[] = $column['name'];
            }
            foreach ($mapping['joinTable']['inverseJoinColumns'] as $column) {
                $column['dataType'] = $this->getObjectManager()
                    ->getClassMetadata($mapping['targetEntity'])
                    ->getTypeOfField($column['referencedColumnName']);
                $fields[] = $column['name'];
            }

            // Get fields and identifiers from target entity
            $auditMetadataFactory = $this->getAuditObjectManager()->getMetadataFactory();
            $auditClassMetadata = $auditMetadataFactory->getMetadataFor($auditClassName);

            $tableName = $config['tableName'];
            $auditTableName = $auditClassMetadata->getTableName();
            $auditDatabase = $this->getAuditObjectManager()->getConnection()->getDatabase();

            $sql .= $this->buildSql($tableName, $auditTableName, $fields, $className);
        }

        // Now iterate through the entities and build trigger code for each.
        foreach ($this->config['entities'] as $className => $options) {
            $auditClassName = $this->getAuditObjectManager()
                ->getRepository('ZF\Doctrine\Audit\Entity\AuditEntity')
                ->generateClassName($className);

            // Get fields and identifiers from target entity
            $metadataFactory = $this->getObjectManager()->getMetadataFactory();
            $auditMetadataFactory = $this->getAuditObjectManager()->getMetadataFactory();
            $classMetadata = $metadataFactory->getMetadataFor($className);
            $auditClassMetadata = $auditMetadataFactory->getMetadataFor($auditClassName);

            $fields = [];

            foreach ($classMetadata->getFieldNames() as $fieldName) {
                $fields[] = $fieldName;
            }

            foreach ($classMetadata->getAssociationMappings() as $mapping) {
                if (! $mapping['isOwningSide'] || isset($mapping['joinTable'])) {
                    continue;
                }

                if (isset($mapping['joinTableColumns'])) {
                    foreach ($mapping['joinTableColumns'] as $field) {
                        $fields[] = $field;
                    }
                } elseif (isset($mapping['joinColumnFieldNames'])) {
                    foreach ($mapping['joinColumnFieldNames'] as $field) {
                        $fields[] = $field;
                    }
                } else {
                    throw new Exception('Unhandled association mapping');
                }
            }

            $tableName = $classMetadata->getTableName();
            $auditTableName = $auditClassMetadata->getTableName();

            $sql .= $this->buildSql($tableName, $auditTableName, $fields, $className);
        }

        $sql .= "\nDELIMITER ;\n";

        return $sql;
    }

    public function buildSql($tableName, $auditTableName, $fields, $className)
    {
        $auditDatabase = $this->getAuditObjectManager()->getConnection()->getDatabase();
        $addSlashesClassName = addslashes($className);

            $sql = <<<EOF

DROP TRIGGER IF EXISTS {$tableName}_insert_audit;//
CREATE TRIGGER {$tableName}_insert_audit
AFTER INSERT ON {$tableName}
FOR EACH ROW
BEGIN

DECLARE isPHP BOOL;
DECLARE closeRevisionAudit BOOL;

INSERT INTO {$auditDatabase}.{$auditTableName} (
EOF;
            $sql .= '`' . implode('`, `', $fields) . '`';
            $sql .= <<<EOF
, revisionEntity_id) VALUES (
EOF;
            $sql .= 'NEW.' . implode(', NEW.', $fields);
            $sql .= <<<EOF
, get_revision_entity_audit('$addSlashesClassName', 'insert')
);

SELECT @zf_doctrine_audit_is_php INTO isPHP;

IF isPHP IS NULL THEN
    SELECT close_revision_audit(0, 'not orm', '', '') INTO closeRevisionAudit;
END IF;

END;//

DROP TRIGGER IF EXISTS {$tableName}_update_audit;//
CREATE TRIGGER {$tableName}_update_audit
AFTER UPDATE ON {$tableName}
FOR EACH ROW
BEGIN

DECLARE isPHP BOOL;
DECLARE closeRevisionAudit BOOL;

INSERT INTO {$auditDatabase}.{$auditTableName} (
EOF;
            $sql .= '`' . implode('`, `', $fields) . '`';
            $sql .= <<<EOF
, revisionEntity_id) VALUES (
EOF;
            $sql .= 'NEW.' . implode(', NEW.', $fields);
            $sql .= <<<EOF
, get_revision_entity_audit('$addSlashesClassName', 'update')
);

SELECT @zf_doctrine_audit_is_php INTO isPHP;

IF isPHP IS NULL THEN
    SELECT close_revision_audit(0, 'not orm', '', '') INTO closeRevisionAudit;
END IF;

END;//

DROP TRIGGER IF EXISTS {$tableName}_delete_audit;//
CREATE TRIGGER {$tableName}_delete_audit
AFTER DELETE ON {$tableName}
FOR EACH ROW
BEGIN

DECLARE isPHP BOOL;
DECLARE closeRevisionAudit BOOL;

INSERT INTO {$auditDatabase}.{$auditTableName} (
EOF;
            $sql .= '`' . implode('`, `', $fields) . '`';
            $sql .= <<<EOF
, revisionEntity_id) VALUES (
EOF;
            $sql .= 'OLD.' . implode(', OLD.', $fields);
            $sql .= <<<EOF
, get_revision_entity_audit('$addSlashesClassName', 'delete')
);

SELECT @zf_doctrine_audit_is_php INTO isPHP;

IF isPHP IS NULL THEN
    SELECT close_revision_audit(0, 'not orm', '', '') INTO closeRevisionAudit;
END IF;

END;//

EOF;

        return $sql;
    }
}
