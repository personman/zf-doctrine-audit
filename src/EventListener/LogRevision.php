<?php

namespace ZF\Doctrine\Audit\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use ZF\Doctrine\Audit\Entity\Revision as RevisionEntity;
use ZF\Doctrine\Audit\Entity\RevisionEntity as RevisionEntityEntity;
use Zend\Code\Reflection\ClassReflection;
use Doctrine\ORM\PersistentCollection;
use Doctrine\Common\Persistence\ObjectManager;
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

    protected $authenticationService;
    protected $revision;
    protected $entities;
    protected $reexchangeEntities;
    protected $collections;
    protected $inAuditTransaction;
    protected $many2many;

    public function getSubscribedEvents()
    {
        return array(
            Events::onFlush,
            Events::postFlush
        );
    }

    public function register()
    {
        $this->getObjectManager()->getEventManager()->addEventSubscriber($this);
    }

    public function setAuthenticationService($service)
    {
        $this->authenticationService = $service;

        return $this;
    }

    public function getAuthenticationService()
    {
        return $this->authenticationService;
    }

    private function setEntities($entities)
    {
        if ($this->entities) {
            return $this;
        }

        $this->entities = $entities;

        return $this;
    }

    private function resetEntities()
    {
        $this->entities = array();

        return $this;
    }

    private function getEntities()
    {
        return $this->entities;
    }

    private function getReexchangeEntities()
    {
        if (!$this->reexchangeEntities) {
            $this->reexchangeEntities = array();
        }

        return $this->reexchangeEntities;
    }

    private function resetReexchangeEntities()
    {
        $this->reexchangeEntities = array();
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
        $this->revisionEntities = array();
    }

    private function getRevisionEntities()
    {
        return $this->revisionEntities;
    }

    public function addCollection($collection)
    {
        if (!$this->collections) {
            $this->collections = array();
        }

        if (in_array($collection, $this->collections, true)) {
            return;
        }

        $this->collections[] = $collection;
    }

    public function getCollections()
    {
        if (!$this->collections) {
            $this->collections = array();
        }

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

    private function getRevision()
    {
        return $this->revision;
    }

    private function resetRevision()
    {
        $this->revision = null;

        return $this;
    }

    // You must flush the revision for the compound audit key to work
    private function buildRevision(ObjectManager $objectManager)
    {
        if ($this->revision) {
            return;
        }

        $revision = new RevisionEntity();

        if ($this->getAuthenticationService()->hasIdentity()) {
            if ($objectManager->contains($this->getAuthenticationService()->getIdentity())) {
                $user = $this->getAuthenticationService()->getIdentity();
            } else {
                $user = $objectManager->merge($this->getAuthenticationService()->getIdentity());
            }

            $revision->setUser($user);
        }

        $revision->setComment($this->getAuditService()->getComment());

        $this->revision = $revision;
    }

    // Reflect audited entity properties
    private function getClassProperties($entity)
    {
        $properties = array();

        $reflectedAuditedEntity = new ClassReflection($entity);

        // Get mapping from metadata

        foreach ($reflectedAuditedEntity->getProperties() as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($entity);

            // If a property is an object we probably are not mapping that to
            // a field.  Do no special handing...
            if ($value instanceof PersistentCollection) {
            }

            // Set values to getId for classes
            if (gettype($value) == 'object' and method_exists($value, 'getId')) {
                $value = $value->getId();
            }

            $properties[$property->getName()] = $value;
        }

        return $properties;
    }

    private function auditEntity($entity, $revisionType)
    {
        $auditEntities = array();

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

        $revisionEntity = new RevisionEntityEntity();
        $revisionEntity->setRevision($this->getRevision());
        $this->getRevision()->getRevisionEntities()->add($revisionEntity);
        $revisionEntity->setRevisionType($revisionType);
        if (method_exists($entity, '__toString')) {
            $revisionEntity->setTitle((string) $entity);
        }
        $this->addRevisionEntity($revisionEntity);

        $auditEntityClass = 'ZF\\Doctrine\\Audit\\Entity\\' . str_replace('\\', '_', get_class($entity));
        $auditEntity = new $auditEntityClass();

        $this->getAuditService()
            ->hydrateAuditEntityFromTargetEntity($auditEntity, $entity);
        $auditEntity->setRevisionEntity($revisionEntity);

        // Re-exchange data after flush to map generated fields
        if ($revisionType ==  'INS' or $revisionType ==  'UPD') {
            $this->addReexchangeEntity(
                array(
                'auditEntity' => $auditEntity,
                'entity' => $entity,
                'revisionEntity' => $revisionEntity,
                )
            );
        } else {
            $revisionEntity->setAuditEntityClass(get_class($auditEntity));
            $revisionEntity->setTargetEntityClass($auditEntity->getAuditedEntityClass());
            $revisionEntity->setEntityKeys($this->getAuditService()->getEntityIdentifierValues($auditEntity));
        }

        $auditEntities[] = $auditEntity;

        return $auditEntities;
    }

    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $entities = array();

        $this->buildRevision($eventArgs->getEntityManager());

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

            // Insert entites will trigger key generation and must be
            // re-exchanged (delete entites go out of scope)
            foreach ($this->getReexchangeEntities() as $entityMap) {
                $entityMap['auditEntity']
                    ->exchangeArray($this->getClassProperties($entityMap['entity']));
                $entityMap['revisionEntity']
                    ->setAuditEntityClass(get_class($entityMap['auditEntity']));
                $entityMap['revisionEntity']
                    ->setTargetEntityClass($entityMap['auditEntity']->getAuditedEntityClass());
                $entityMap['revisionEntity']
                    ->setEntityKeys($this->getAuditService()->getEntityIdentifierValues($entityMap['auditEntity']));
            }

            // Flush revision and revisionEntities
            $this->getAuditObjectManager()->persist($this->getRevision());
            foreach ($this->getRevisionEntities() as $entity) {
                $this->getAuditObjectManager()->persist($entity);
            }

            $this->getAuditObjectManager()->flush();

            foreach ($this->getEntities() as $entity) {
                $this->getAuditObjectManager()->persist($entity);
            }

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
