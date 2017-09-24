<?php

namespace ZF\Doctrine\Audit\Mapping\Driver;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use ZF\Doctrine\Audit\Persistence;
use ZF\Doctrine\Audit\Entity;
use Exception;

class JoinEntityDriver implements
    MappingDriver,
    Persistence\JoinEntityConfigCollectionAwareInterface,
    Persistence\ObjectManagerAwareInterface,
    Persistence\AuditObjectManagerAwareInterface,
    Persistence\AuditOptionsAwareInterface
{
    use Persistence\JoinEntityConfigCollectionAwareTrait;
    use Persistence\ObjectManagerAwareTrait;
    use Persistence\AuditObjectManagerAwareTrait;
    use Persistence\AuditOptionsAwareTrait;

    public function register(): self
    {
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
        foreach ($this->getJoinEntityConfigCollection() as $targetClassName => $config) {
            if ($this->getAuditObjectManager()
                ->getRepository(Entity\AuditEntity::class)
                ->generateClassName($targetClassName) == $className
            ) {
                $foundClassName = true;
                break;
            }
        }

        if (! $foundClassName) {
            throw new \Exception('join entity autoloader not found: ' . $auditClassName);
            return false;
        }

        $metadataFactory = $this->getObjectManager()->getMetadataFactory();
        $builder = new ClassMetadataBuilder($metadata);
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
            $fields[] = $column;
        }
        foreach ($mapping['joinTable']['inverseJoinColumns'] as $column) {
            $column['dataType'] = $this->getObjectManager()
                ->getClassMetadata($mapping['targetEntity'])
                ->getTypeOfField($column['referencedColumnName']);
            $fields[] = $column;
        }

        $identifiers = [];
        foreach ($fields as $field) {
            $builder->addField(
                $field['name'],
                $field['dataType'],
                [
                // 'nullable' => $config['nullable'],
                    'columnName' => $field['name'],
                    'id' => true,
                ]
            );
        }

        $association = $builder->createManyToOne('revisionEntity', Entity\RevisionEntity::class);
        $association->makePrimaryKey();
        $association->build();

        $builder->setTable(
            $this->getAuditOptions()->getAuditTableNamePrefix()
            . $config['tableName']
            . $this->getAuditOptions()->getAuditTableNameSuffix()
        );
    }

    /**
     * Gets the names of all mapped classes known to this driver.
     *
     * @return array The names of all mapped classes known to this driver.
     */
    public function getAllClassNames(): array
    {
        $auditEntityRepository = $this->getAuditObjectManager()
            ->getRepository('ZF\Doctrine\Audit\Entity\AuditEntity');

        $classNames = [];
        foreach ($this->getJoinEntityConfigCollection() as $className => $config) {
            $classNames[] = $auditEntityRepository->generateClassName($className);
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
