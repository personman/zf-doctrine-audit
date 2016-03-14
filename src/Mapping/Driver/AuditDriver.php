<?php

namespace ZF\Doctrine\Audit\Mapping\Driver;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use ZF\Doctrine\Audit\Persistence;
use Exception;

class AuditDriver implements
    MappingDriver,
    Persistence\AuditEntitiesAwareInterface,
    Persistence\ObjectManagerAwareInterface,
    Persistence\AuditObjectManagerAwareInterface,
    Persistence\AuditOptionsAwareInterface
{
    use Persistence\AuditEntitiesAwareTrait;
    use Persistence\ObjectManagerAwareTrait;
    use Persistence\AuditObjectManagerAwareTrait;
    use Persistence\AuditOptionsAwareTrait;

    public function register()
    {
        $this->getAuditObjectManager()->getConfiguration()->getMetadataDriverImpl()
            ->addDriver($this, 'ZF\Doctrine\Audit\Entity');

        return $this;
    }

    /**
     * Load the metadata for the specified class into the provided container.
     *
     * @param string        $className
     * @param ClassMetadata $metadata
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
        $metadataFactory = $this->getObjectManager()->getMetadataFactory();
        $builder = new ClassMetadataBuilder($metadata);

        if ($className == 'ZF\\Doctrine\\Audit\\Entity\RevisionEntity') {
            $builder->createField('id', 'bigint')->isPrimaryKey()->generatedValue()->build();
            $builder->addManyToOne('revision', 'ZF\Doctrine\Audit\\Entity\\Revision', 'revisionEntities');
            $builder->addField('entityKeys', 'string');
            $builder->addField('auditEntityClass', 'string');
            $builder->addField('targetEntityClass', 'string');
            $builder->addField('revisionType', 'string');
            $builder->addField('title', 'string', array('nullable' => true));

            $metadata->setTableName($this->getAuditOptions()['revision_entity_table_name']);

            return;
        }

        // Revision is managed here rather than a separate namespace and driver
        if ($className == 'ZF\\Doctrine\\Audit\\Entity\\Revision') {
            $builder->createField('id', 'bigint')->isPrimaryKey()->generatedValue()->build();
            $builder->addField('comment', 'text', array('nullable' => true));
            $builder->addField('timestamp', 'datetime');

            // Add association between RevisionEntity and Revision
            $builder->addOneToMany('revisionEntities', 'ZF\Doctrine\Audit\\Entity\\RevisionEntity', 'revision');

# FIXME:  use ids, not entities
// Add assoication between User and Revision
// $userMetadata = $metadataFactory->getMetadataFor($moduleOptions->getUserEntityClassName());
// $builder
// ->createManyToOne('user', $userMetadata->getName())
// ->addJoinColumn('user_id', $userMetadata->getSingleIdentifierColumnName())
// ->build();

            $metadata->setTableName($this->getAuditOptions()['revision_table_name']);

            return;
        }

        $identifiers = array();

        // Get the entity this entity audits
        $metadataClassName = $metadata->getName();
        $metadataClass = new $metadataClassName();

        $auditedClassMetadata = $metadataFactory->getMetadataFor($metadataClass->getAuditedEntityClass());

        $builder->addManyToOne('revisionEntity', 'ZF\\Doctrine\\Audit\\Entity\\RevisionEntity');
        $identifiers[] = 'revisionEntity';

        // Add fields from target to audit entity
        foreach ($auditedClassMetadata->getFieldNames() as $fieldName) {
            $builder->addField(
                $fieldName,
                $auditedClassMetadata->getTypeOfField($fieldName),
                array(
                    'columnName' => $auditedClassMetadata->getColumnName($fieldName),
                    'nullable' => true,
                    'quoted' => true
                )
            );

            if ($auditedClassMetadata->isIdentifier($fieldName)) {
                $identifiers[] = $fieldName;
            }
        }

        foreach ($auditedClassMetadata->getAssociationMappings() as $mapping) {
            if (! $mapping['isOwningSide'] || isset($mapping['joinTable'])) {
                continue;
            }

            if (isset($mapping['joinTableColumns'])) {
                foreach ($mapping['joinTableColumns'] as $field) {
                    // FIXME:  set data type correct for mapping info
                    $builder->addField(
                        $mapping['fieldName'],
                        'bigint',
                        array('nullable' => true, 'columnName' => $field)
                    );
                }
            } elseif (isset($mapping['joinColumnFieldNames'])) {
                foreach ($mapping['joinColumnFieldNames'] as $field) {
                    // FIXME:  set data type correct for mapping info
                    $builder->addField(
                        $mapping['fieldName'],
                        'bigint',
                        array('nullable' => true, 'columnName' => $field)
                    );
                }
            } else {
                throw new Exception('Unhandled association mapping');
            }
        }

        $metadata->setTableName(
            $this->getAuditOptions()['audit_table_name_prefix']
                . $auditedClassMetadata->getTableName()
                . $this->getAuditOptions()['audit_table_name_suffix']
        );
        $metadata->setIdentifier($identifiers);

        return;
    }

    /**
     * Gets the names of all mapped classes known to this driver.
     *
     * @return array The names of all mapped classes known to this driver.
     */
    public function getAllClassNames(): array
    {
        $objectManager = $this->getObjectManager();
        $metadataFactory = $objectManager->getMetadataFactory();

        $auditEntities = array();
        foreach ($this->getAuditEntities() as $name => $auditEntityOptions) {
            $auditClassName = "ZF\\Doctrine\\Audit\\Entity\\" . str_replace('\\', '_', $name);
            $auditEntities[] = $auditClassName;
            $auditedClassMetadata = $metadataFactory->getMetadataFor($name);

            // FIXME:  done in autoloader
            foreach ($auditedClassMetadata->getAssociationMappings() as $mapping) {
                if (isset($mapping['joinTable']['name'])) {
                    $auditJoinTableClassName = "ZF\\Doctrine\\Audit\\Entity\\"
                        . str_replace('\\', '_', $mapping['joinTable']['name']);
                    $auditEntities[] = $auditJoinTableClassName;
                    // $moduleOptions->addJoinClass($auditJoinTableClassName, $mapping);
                }
            }
        }

        // Add revision (manage here rather than separate namespace)
        $auditEntities[] = 'ZF\\Doctrine\\Audit\\Entity\\Revision';
        $auditEntities[] = 'ZF\\Doctrine\\Audit\\Entity\\RevisionEntity';

        return $auditEntities;
    }

    /**
     * Whether the class with the specified name should have its metadata loaded.
     * This is only the case if it is either mapped as an Entity or a
     * MappedSuperclass.
     *
     * @param  string $className
     * @return boolean
     */
    public function isTransient($className): bool
    {
        return true;
    }
}
