<?php

namespace ZF\Doctrine\Audit\Tools\TriggerGenerator;

use ZF\Doctrine\Audit\Persistence\AuditObjectManagerAwareInterface;
use ZF\Doctrine\Audit\Persistence\AuditObjectManagerAwareTrait;
use ZF\Doctrine\Audit\Persistence\ObjectManagerAwareInterface;
use ZF\Doctrine\Audit\Persistence\ObjectManagerAwareTrait;
use Doctrine\Common\Persistence\ObjectManager;
use Exception;

class MySQL implements
    AuditObjectManagerAwareInterface,
    ObjectManagerAwareInterface
{
    use AuditObjectManagerAwareTrait;
    use ObjectManagerAwareTrait;

    private $config;

    public function __construct(ObjectManager $objectManager, ObjectManager $auditObjectManager, array $config)
    {
        $this->setObjectManager($objectManager);
        $this->setAuditObjectManager($auditObjectManager);
        $this->config = $config;

        if ($this->getObjectManager()->getConnection()->getDatabasePlatform()->getName() !== 'mysql')
        {
            throw new Exception('Invalid database platform for MySQL trigger generator');
        }
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
CREATE FUNCTION get_revision_entity_audit(p_targetEntity varchar(255), p_revisionType varchar(255))
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

        // Now iterate through the entities and build trigger code for each.
        foreach ($this->config['entities'] as $className => $options) {
            $auditClassName = $this->getAuditObjectManager()
                ->getRepository('ZF\Doctrine\Audit\Entity\AuditEntity')
                ->generateClassName($className)
                ;

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
            $auditDatabase = $this->getAuditObjectManager()->getConnection()->getDatabase();
            $addSlashesClassName = addslashes($className);

            $sql .= <<<EOF

DROP TRIGGER IF EXISTS {$tableName}_insert_audit;//
CREATE TRIGGER {$tableName}_insert_audit
AFTER INSERT ON {$tableName}
FOR EACH ROW
BEGIN

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
END;//

DROP TRIGGER IF EXISTS {$tableName}_update_audit;//
CREATE TRIGGER {$tableName}_update_audit
AFTER INSERT ON {$tableName}
FOR EACH ROW
BEGIN

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
END;//

DROP TRIGGER IF EXISTS {$tableName}_delete_audit;//
CREATE TRIGGER {$tableName}_delete_audit
AFTER DELETE ON {$tableName}
FOR EACH ROW
BEGIN

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
END;//

EOF;

        }

        $sql .= "\nDELIMITER ;\n";

        return $sql;
    }
}