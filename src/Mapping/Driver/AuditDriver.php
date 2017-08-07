<?php

namespace ZF\Doctrine\Audit\Mapping\Driver;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
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
        // Driver for zf-doctrine-audit entites
        $xmlDriver = new XmlDriver(__DIR__ . '/../../../config/orm');
        $this->getAuditObjectManager()
            ->getConfiguration()
            ->getMetadataDriverImpl()
            ->addDriver($xmlDriver, 'ZF\Doctrine\Audit\Entity');

        // Driver for audited entities
        $this->getAuditObjectManager()
            ->getConfiguration()
            ->getMetadataDriverImpl()
            ->addDriver($this, 'ZF\Doctrine\Audit\RevisionEntity');

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

        $identifiers = array();

        // Get the entity this entity audits
        $metadataClassName = $metadata->getName();
        $metadataClass = new $metadataClassName();

        $auditedClassMetadata = $metadataFactory->getMetadataFor($metadataClass->getAuditedEntityClass());

        // Is the passed class name a regular entity?
        if (! in_array($metadataClass->getAuditedEntityClass(), array_keys($this->getAuditEntities()))) {
            die($className . ' not found in loadMetadataForClass');
        }

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
        $classNames = [];
        foreach ($this->getAuditEntities() as $className => $options) {
            $classNames[] = $this->getAuditObjectManager()
                ->getRepository('ZF\Doctrine\Audit\Entity\AuditEntity')
                ->generateClassName($className);
        }

        return $classNames;
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
