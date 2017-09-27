<?php

namespace ZF\Doctrine\Audit\Fixture;

use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\Common\Collections\ArrayCollection;
use ZF\Doctrine\Audit\Entity;
use ZF\Doctrine\Audit\Persistence\JoinEntityConfigCollectionAwareInterface;
use ZF\Doctrine\Audit\Persistence\JoinEntityConfigCollectionAwareTrait;
use ZF\Doctrine\Audit\Persistence\ObjectManagerAwareInterface;
use ZF\Doctrine\Audit\Persistence\ObjectManagerAwareTrait;

class RevisionJoinEntityFixture implements
    FixtureInterface,
    JoinEntityConfigCollectionAwareInterface,
    ObjectManagerAwareInterface
{
    use JoinEntityConfigCollectionAwareTrait;
    use ObjectManagerAwareTrait;

    public function load(ObjectManager $auditObjectManager)
    {
        foreach ($this->getJoinEntityConfigCollection() as $className => $config) {
            $targetEntity = $auditObjectManager
                ->getRepository(Entity\TargetEntity::class)
                ->findOneBy(['name' => $className]);

            if (! $targetEntity) {
                $auditEntityClassName = $auditObjectManager
                    ->getRepository(Entity\AuditEntity::class)
                    ->generateClassName($className);

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
                $targetEntity->setTableName($config['tableName']);

                $auditObjectManager->persist($auditEntity);
                $auditObjectManager->persist($targetEntity);
            }
        }

        $auditObjectManager->flush();
    }
}
