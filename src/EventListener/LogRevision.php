<?php

namespace ZF\Doctrine\Audit\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\PersistentCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Zend\Code\Reflection\ClassReflection;
use ZF\Doctrine\Audit\Entity;
use ZF\Doctrine\Audit\Persistence;

class LogRevision implements
    EventSubscriber,
    Persistence\AuditEntitiesAwareInterface,
    Persistence\AuditServiceAwareInterface,
    Persistence\AuditObjectManagerAwareInterface,
    Persistence\ObjectManagerAwareInterface,
    Persistence\AuthenticationServiceAwareInterface
{
    use Persistence\ObjectManagerAwareTrait;
    use Persistence\AuditEntitiesAwareTrait;
    use Persistence\AuditObjectManagerAwareTrait;
    use Persistence\AuditServiceAwareTrait;
    use Persistence\AuthenticationServiceAwareTrait;

    protected $revision;
    protected $entities;
    protected $reexchangeEntities;
    protected $collections = [];
    protected $inAuditTransaction;
    protected $many2many;

    public function register()
    {
        $this->getObjectManager()->getEventManager()->addEventSubscriber($this);

        return $this;
    }

    public function getSubscribedEvents(): array
    {
        return array(
            Events::onFlush,
            Events::postFlush
        );
    }

    private function setEntities(array $entities)
    {
        $this->entities ?? $this->entities = $entities;

        return $this;
    }

    private function resetEntities()
    {
        $this->entities = [];

        return $this;
    }

    private function getEntities(): array
    {
        return $this->entities;
    }

    private function getReexchangeEntities()
    {
        if (! $this->reexchangeEntities) {
            $this->reexchangeEntities = [];
        }

        return $this->reexchangeEntities;
    }

    private function resetReexchangeEntities()
    {
        $this->reexchangeEntities = [];
    }

    private function addReexchangeEntity($entityMap)
    {
        $this->reexchangeEntities[] = $entityMap;
    }

    private function addRevisionEntity(RevisionEntityEntity $entity)
    {
        $this->revisionEntities[] = $entity;
    }

    private function resetRevisionEntities()
    {
        $this->revisionEntities = [];
    }

    private function getRevisionEntities()
    {
        return $this->revisionEntities;
    }

    public function addCollection($collection)
    {
        if (in_array($collection, $this->collections, true)) {
            return $this;
        }

        $this->collections[] = $collection;

        return $this;
    }

    public function getCollections()
    {
        return $this->collections;
    }

    public function setInAuditTransaction($setting)
    {
        $this->inAuditTransaction = $setting;

        return $this;
    }

    public function getInAuditTransaction()
    {
        return $this->inAuditTransaction;
    }

    private function resetRevision()
    {
        $this->revision = null;
    }

    // You must flush the revision for the compound audit key to work
    private function getRevision(ObjectManager $objectManager): Entity\Revision
    {
        if ($this->revision) {
            return $this->revision;
        }

        $this->revision = new Entity\Revision();
        $this->revision->setTimestamp(new DateTime());
        $this->revision->setComment($this->getAuditService()->getComment());

        if ($this->getAuthenticationService()->hasIdentity()) {
            $user = $this->getAuthenticationService()->getIdentity();
            $this->revision->setUserId($user->getId());
        }

        return $this->revision;
    }

    // Reflect audited entity properties
    private function getClassProperties($entity)
    {
        $hydrator = new DoctrineHydrator($this->getObjectManger());

        $properties = [];
        foreach ($hydrator->extract($entity) as $property => $value) {
            // Set values to getId for classes
            if (gettype($value) == 'object' and method_exists($value, 'getId')) {
                $value = $value->getId();
            }

            $properties[$property] = $value;
        }

        return $properties;
    }

    private function auditEntity($entity, Entity\RevisionType $revisionType)
    {
        // Entities may be proxy objects
        $found = false;
        foreach ($this->getAuditEntities() as $auditEntityClassName => $auditEntityOptions) {
            if ($entity instanceof $auditEntityClassName) {
                $found = true;
                break;
            }
        }
        if (! $found) {
            return [];
        }

        $targetEntity = $this->getAuditObjectManager()
            ->getRepository('ZF\Doctrine\Audit\Entity\TargetEntity')
            ->findOneBy([
                'name' => get_class($entity)
            ]);

        $revision = $this->getRevision();
        $revisionEntity = new Entity\RevisionEntity();
        $revisionEntity->setRevision($revision);
        $revisionEntity->setRevisionType($revisionType);
        $revisionEntity->setTargetEntity($targetEntity);
        if (method_exists($entity, '__toString')) {
            $revisionEntity->setTitle((string) $entity);
        }

        $this->addRevisionEntity($revisionEntity);

        $auditEntityClass = $targetEntity->getAuditEntity()->getName();
        $auditEntity = new $auditEntityClass();
        $auditEntity->setRevisionEntity($revisionEntity);

        $this->getAuditService()
            ->hydrateAuditEntityFromTargetEntity($auditEntity, $entity);

        // Re-exchange data after flush to map generated fields
        if ($revisionType->getRevisionType() == 'insert'
               || $revisionType->getRevisionType() ==  'update') {
            $this->addReexchangeEntity([
                'auditEntity' => $auditEntity,
                'entity' => $entity,
            ]);
        } else {
            $properties = $this->getClassProperties($entity);
            foreach ($targetEntity->getIdentifier() as $identifier) {
                $revisionEntityIdentifierValue = new Entity\RevisionEntityIdentifierValue;
                $revisionEntityIdentifierValue
                    ->setRevisionEntity($revisionEntity)
                    ->setIdentifier($identifier)
                    ->setValue($properties[$identifier->getFieldName()])
                    ;
                $this->getAuditObjectManager()->persist($revisionEntityIdentifierValue);
            }
        }

        $this->getAuditObjectManager()->persist($auditEntity);
        $this->getAuditObjectManager()->persist($revisionEntity);

        return $auditEntity;
    }

    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $entities = array();

        $this->getRevision($eventArgs->getEntityManager());

        foreach ($eventArgs->getEntityManager()->getUnitOfWork()->getScheduledEntityInsertions() as $entity) {
            $entities = array_merge($entities, $this->auditEntity($entity, 'INS'));
        }

        foreach ($eventArgs->getEntityManager()->getUnitOfWork()->getScheduledEntityUpdates() as $entity) {
            $entities = array_merge($entities, $this->auditEntity($entity, 'UPD'));
        }

        foreach ($eventArgs->getEntityManager()->getUnitOfWork()->getScheduledEntityDeletions() as $entity) {
            $entities = array_merge($entities, $this->auditEntity($entity, 'DEL'));
        }

        foreach ($eventArgs->getEntityManager()
            ->getUnitOfWork()
            ->getScheduledCollectionDeletions() as $collectionToDelete) {
            if ($collectionToDelete instanceof PersistentCollection) {
                $this->addCollection($collectionToDelete);
            }
        }

        foreach ($eventArgs->getEntityManager()
            ->getUnitOfWork()
            ->getScheduledCollectionUpdates() as $collectionToUpdate) {
            if ($collectionToUpdate instanceof PersistentCollection) {
                $this->addCollection($collectionToUpdate);
            }
        }

        $this->setEntities($entities);
    }

    public function postFlush(PostFlushEventArgs $args)
    {
        if ($this->getEntities() and !$this->getInAuditTransaction()) {
            $this->setInAuditTransaction(true);

            $this->getAuditObjectManager()->beginTransaction();

            // insert | update entites will trigger key generation and must be
            // re-exchanged (delete entites go out of scope)
            foreach ($this->getReexchangeEntities() as $entityMap) {
                $properties = $this->getClassProperties($entityMap['entity']);
                $revisionEntity = $entityMap['auditEntity']
                    ->getRevisionEntity();

                foreach ($revisionEntity->getTargetEntity()->getIdentifier() as $identifier) {
                    $revisionEntityIdentifierValue = new Entity\RevisionEntityIdentifierValue;
                    $revisionEntityIdentifierValue
                        ->setRevisionEntity($revisionEntity)
                        ->setIdentifier($identifier)
                        ->setValue($properties[$identifier->getFieldName()])
                        ;
                   $this->getAuditObjectManager()->persist($revisionEntityIdentifierValue);
                }
            }

            // Flush revision and revisionEntities
            $this->getAuditObjectManager()->flush();
            $this->getAuditObjectManager()->commit();

            $this->resetEntities();
            $this->resetReexchangeEntities();
            $this->resetRevision();
            $this->resetRevisionEntities();
            $this->setInAuditTransaction(false);
        }
    }
}
