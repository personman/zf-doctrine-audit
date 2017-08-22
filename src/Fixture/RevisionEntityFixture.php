<?php

namespace ZF\Doctrine\Audit\Fixture;

use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\Common\Collections\ArrayCollection;
use ZF\Doctrine\Audit\Entity;
use ZF\Doctrine\Audit\Persistence\EntityConfigCollectionAwareInterface;
use ZF\Doctrine\Audit\Persistence\EntityConfigCollectionAwareTrait;
use ZF\Doctrine\Audit\Persistence\ObjectManagerAwareInterface;
use ZF\Doctrine\Audit\Persistence\ObjectManagerAwareTrait;

class RevisionEntityFixture implements
    FixtureInterface,
    EntityConfigCollectionAwareInterface,
    ObjectManagerAwareInterface
{
    use EntityConfigCollectionAwareTrait;
    use ObjectManagerAwareTrait;

    public function load(ObjectManager $auditObjectManager)
    {
        // Create a revision to associate with field revision
        $revision = new Entity\Revision();
        $revision->setCreatedAt(new DateTime());
        $revision->setComment('Data Fixture Import');

        $auditObjectManager->persist($revision);

        foreach ($this->getEntityConfigCollection() as $className => $route) {
            $targetEntity = $auditObjectManager
                ->getRepository(Entity\TargetEntity::class)
                ->findOneBy(['name' => $className]);

            if (! $targetEntity) {
                $auditEntityClassName = $auditObjectManager
                    ->getRepository(Entity\AuditEntity::class)
                    ->generateClassName($className)
                    ;

                $auditObjectManager->getClassMetadata($auditEntityClassName);

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
                    $this->getObjectManager()
                        ->getClassMetadata($className)
                        ->getTableName()
                );

                $identifiers = $this->getObjectManager()
                    ->getClassMetadata($className)
                    ->getIdentifierFieldNames()
                    ;

                foreach ($identifiers as $fieldName) {
                    $identifier = new Entity\Identifier();
                    $identifier->setTargetEntity($targetEntity);
                    $identifier->setFieldName($fieldName);
                    $identifier->setColumnName(
                        $this->getObjectManager()
                            ->getClassMetadata($className)
                            ->getColumnName($fieldName)
                    );

                    $auditObjectManager->persist($identifier);
                }

                // Add Join Columns as Target Entities
                $associations = $this->getObjectManager()
                    ->getClassMetadata($className)
                    ->getAssociationNames()
                    ;

                foreach ($associations as $fieldName) {
                    $associationMapping = $this->getObjectManager()
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
    }
}
