<?php

namespace ZF2EntityAudit\EventListener;

use Doctrine\Common\EventSubscriber
    , Doctrine\ORM\UnitOfWork
    , Doctrine\ORM\EntityManager
    , Doctrine\ORM\Events
    , Doctrine\ORM\Event\OnFlushEventArgs
    , Doctrine\ORM\Event\PostFlushEventArgs
    , Doctrine\ORM\Event\LifecycleEventArgs
    , Zend\ServiceManager\ServiceManager
    , ZF2EntityAudit\Entity\Revision as RevisionEntity
    , ZF2EntityAudit\Options\ModuleOptions
    , ZF2EntityAudit\Entity\RevisionEntity as RevisionEntityEntity
    , Zend\Code\Reflection\ClassReflection;
    ;

class LogRevision implements EventSubscriber
{
    private $entityManager;
    private $serviceManager;
    private $config;
    private $revision;
    private $entities;
    private $insertEntities;
    protected $committingRevision;

    public function __construct($serviceManager)
    {
        $this->setServiceManager($serviceManager);
        $this->setEntityManager($this->getServiceManager()->get("doctrine.entitymanager.orm_default"));
        $this->setConfig($this->getServiceManager()->get("auditModuleOptions"));
    }

    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }

    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        return $this;
    }

    public function getEntityManager()
    {
        return $this->entityManager;
    }

    public function setConfig(ModuleOptions $config)
    {
        $this->config = $config;
        return $this;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getSubscribedEvents()
    {
        return array(
            Events::onFlush
            , Events::postFlush
        );
    }

    private function resetRevision()
    {
        $this->revision = null;
        return $this;
    }

    private function getRevision()
    {
        return $this->revision;
    }

    private function commitRevision()
    {
        $this->committingRevision = true;
        $this->getEntityManager()->persist($this->getRevision());
        $this->getEntityManager()->flush();
        $this->committingRevision = false;
    }

    private function setEntities($entities)
    {
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

    private function getInsertEntities()
    {
        if (!$this->insertEntities) $this->insertEntities = array();
        return $this->insertEntities;
    }

    private function resetInsertEntities()
    {
        $this->insertEntities = array();
    }

    private function addInsertEntity($entityMap)
    {
        $this->insertEntities[] = $entityMap;
    }

    // You must flush the revision for the compound audit key to work
    private function buildRevision() {
        $revision = new RevisionEntity();
        if ($this->getConfig()->getUser()) $revision->setUser($this->getConfig()->getUser());
        $revision->setComment($this->getServiceManager()->get('auditService')->getComment());

        $this->revision = $revision;
    }

    // Reflect audited entity properties
    private function getClassProperties($entity)
    {
        $properties = array();

        $reflectedAuditedEntity = new ClassReflection($entity);
        foreach($reflectedAuditedEntity->getProperties() as $property) {
            $property->setAccessible(true);
            $properties[$property->getName()] = $property->getValue($entity);
        }

        return $properties;
    }

    // Copy all properties from entity to it's audited version and persist
    private function auditEntity($entity, $revisionType)
    {
        if (!in_array(get_class($entity), $this->getConfig()->getAuditedEntityClasses()))
            return array();

        $auditEntityClass = 'ZF2EntityAudit\\Entity\\' . str_replace('\\', '_', get_class($entity));
        $auditEntity = new $auditEntityClass();
        $auditEntity->exchangeArray($this->getClassProperties($entity));

        $revisionSetter = 'set' . $this->getConfig()->getRevisionFieldName();
        $auditEntity->$revisionSetter($this->getRevision());

        $revisionEntity = new RevisionEntityEntity();
        $revisionEntity->setRevision($this->getRevision());
        $revisionEntity->setRevisionType($revisionType);

        if ($revisionType ==  'INS') {
            $this->addInsertEntity(array(
                'auditEntity' => $auditEntity,
                'entity' => $entity,
                'revisionEntity' => $revisionEntity,
            ));
        } else {
            $revisionEntity->setAuditEntity($auditEntity);
        }

        return array($auditEntity, $revisionEntity);
    }

    /**
     * This is the workhorse of this class.
     *
     * An audit begins with a fresh unit of work.  The unit of work
     * is stored on the class and after it has been commited
     * the postFlush walks through the entities it and audits them.
     * Auditing requires to flush()es, one for the Revison and one
     * for all the audited entities and revision entities
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if ($this->getEntities()) {
            $this->commitRevision();

            // Insert entites will trigger key generation and must be
            // re-exchanged
            foreach ($this->getInsertEntities() as $entityMap) {
                $entityMap['auditEntity']->exchangeArray($this->getClassProperties($entityMap['entity']));
                $entityMap['revisionEntity']->setAuditEntity($entityMap['auditEntity']);
            }

            foreach ($this->getEntities() as $entity)
                $this->getEntityManager()->persist($entity);

            $this->resetEntities();
            $this->getEntityManager()->flush();
        }

        $this->resetEntities();
        $this->resetInsertEntities();
        $this->resetRevision();
    }

    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $entities = array();

        if ($this->committingRevision) return;

        $this->buildRevision();

        foreach ($eventArgs->getEntityManager()->getUnitOfWork()->getScheduledEntityInsertions() AS $entity) {
            $entities = array_merge($entities, $this->auditEntity($entity, 'INS'));
        }

        foreach ($eventArgs->getEntityManager()->getUnitOfWork()->getScheduledEntityUpdates() AS $entity) {
            $entities = array_merge($entities, $this->auditEntity($entity, 'UPD'));
        }

        foreach ($eventArgs->getEntityManager()->getUnitOfWork()->getScheduledEntityDeletions() AS $entity) {
            $entities = array_merge($entities, $this->auditEntity($entity, 'DEL'));
        }

        if ($entities) {
            $this->setEntities($entities);
        }
    }
}
