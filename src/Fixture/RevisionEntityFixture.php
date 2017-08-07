<?php

namespace ZF\Doctrine\Audit\Fixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use ZF\Doctrine\Audit\Entity;
use DateTime;

use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\Common\Collections\ArrayCollection;

class RevisionEntityFixture implements
    FixtureInterface
{
    public $config;
    public $objectManager;

    public function load(ObjectManager $auditObjectManager)
    {
        $joinTableCollection = new ArrayCollection();

        // Create a revision to associate with field revision
        $revision = new Entity\Revision();
        $revision->setCreatedAt(new DateTime());
        $revision->setComment('Data Fixture Import');

        $auditObjectManager->persist($revision);

        foreach ($this->config['entities'] as $className => $route) {
            $targetEntity = $auditObjectManager
                ->getRepository('ZF\Doctrine\Audit\Entity\TargetEntity')
                ->findOneBy(['name' => $className]);

            if (! $targetEntity) {
                $auditEntityClassName = $auditObjectManager
                    ->getRepository('ZF\Doctrine\Audit\Entity\AuditEntity')
                    ->generateClassName($className)
                    ;

                // Is this a many to many mapping?
                try {
                    $auditObjectManager->getClassMetadata($auditEntityClassName);
                } catch (MappingException $e) {
                    // The entity does not exist and this is probably a many to many
                    $joinTableCollection->add($className);
                    continue;
                }

                $auditEntity = new Entity\AuditEntity();
                $auditEntity->setName($auditEntityClassName);
                $auditEntity->setTableName(
                    $auditObjectManager
                        ->getClassMetadata($auditEntityClassName)
                        ->getTableName()
                );

                $targetEntity = new Entity\TargetEntity();
                $targetEntity->setAuditEntity($auditEntity);
                $targetEntity->setName($className);
                $targetEntity->setTableName(
                    $this->objectManager
                        ->getClassMetadata($className)
                        ->getTableName()
                );

                $identifiers = $this->objectManager
                    ->getClassMetadata($className)
                    ->getIdentifierFieldNames()
                    ;

                foreach ($identifiers as $fieldName) {
                    $identifier = new Entity\Identifier();
                    $identifier->setTargetEntity($targetEntity);
                    $identifier->setFieldName($fieldName);
                    $identifier->setColumnName(
                        $this->objectManager
                            ->getClassMetadata($className)
                            ->getColumnName($fieldName)
                    );

                    $auditObjectManager->persist($identifier);
                }

                // Add Join Columns as Target Entities
                $associations = $this->objectManager
                    ->getClassMetadata($className)
                    ->getAssociationNames()
                    ;

                foreach ($associations as $fieldName) {
                    $associationMapping = $this->objectManager
                        ->getClassMetadata($className)
                        ->getAssociationMapping($fieldName);

                    if (! isset($associationMapping['joinColumns'])) {
                        continue;
                    }
                }

                $auditObjectManager->persist($auditEntity);
                $auditObjectManager->persist($targetEntity);
            }
        }
        $auditObjectManager->flush();

        foreach ($joinTableCollection as $joinTableClassName) {
            $this->mapJoinTable($auditObjectManager, $joinTableClassName);
        }
//        $auditObjectManager->flush();
    }

    private function mapJoinTable(ObjectManager $auditObjectManager, $joinTableClassName)
    {
        $namespaceParts = explode('\\', $joinTableClassName);
        $tableName = array_pop($namespaceParts);

        $foundJoinTable = false;
        foreach ($this->config['entities'] as $className => $route) {
            // The same error will happen here for this invalid class name so catch and release
            try {
                $metadata = $this->objectManager->getClassMetadata($className);
            } catch (MappingException $e) {
                continue;
            }

            foreach ($metadata->getAssociationMappings() as $mapping) {
                if (isset($mapping['joinTable'])) {
                    if ($mapping['joinTable']['name'] == $tableName) {
                        $foundJoinTable = true;
                        break;
                    }
                }
            }

            if ($foundJoinTable) {
                break;
            }
        }

        if (! $foundJoinTable) {
            throw new MappingException('JoinTable for mapping ' . $joinTableClassName . ' does not exist.');
        }

        // Find parent AuditEntity
        $parent = $auditObjectManager
            ->getRepository(Entity\AuditEntity::class)
            ->findOneBy([
                'name' => $mapping['targetEntity'],
            ]);

        $auditEntityClassName = $auditObjectManager
            ->getRepository('ZF\Doctrine\Audit\Entity\AuditEntity')
            ->generateClassName($joinTableClassName)
            ;

        $auditEntity = new Entity\AuditEntity();
        $auditEntity->setName($auditEntityClassName);
        $auditEntity->setTableName(
            $auditObjectManager
                ->getClassMetadata($auditEntityClassName)
                ->getTableName()
        );

        $targetEntity = new Entity\TargetEntity();
        $targetEntity->setAuditEntity($auditEntity);
        $targetEntity->setName($className);
        $targetEntity->setTableName($tableName);
        $targetEntity->setIsJoinTable(true);
        $targetEntity->setParent($parent);

        $auditObjectManager->persist($auditEntity);
        $auditObjectManager->persist($targetEntity);

    }
}
