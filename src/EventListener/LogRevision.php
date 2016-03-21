<?php

namespace ZF\Doctrine\Audit\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\PersistentCollection;
use ZF\Doctrine\Audit\Entity;
use ZF\Doctrine\Audit\Persistence;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use DateTime;

class LogRevision implements
    EventSubscriber,
    Persistence\AuditEntitiesAwareInterface,
    Persistence\AuditObjectManagerAwareInterface,
    Persistence\ObjectManagerAwareInterface,
    Persistence\AuthenticationServiceAwareInterface,
    Persistence\RevisionCommentAwareInterface
{
    use Persistence\ObjectManagerAwareTrait;
    use Persistence\AuditEntitiesAwareTrait;
    use Persistence\AuditObjectManagerAwareTrait;
    use Persistence\AuthenticationServiceAwareTrait;
    use Persistence\RevisionCommentAwareTrait;

    protected $revision;
    protected $queue = [];

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

    /**
     * Insert and update entities are queued here from onFlush
     * for processing in postFlush
     */
    private function getQueue()
    {
        return $this->queue;
    }

    private function enqueue(array $entityMap)
    {
        $this->queue[] = $entityMap;

        return $this;
    }

    private function getRevision(): Entity\Revision
    {
        if ($this->revision) {
            return $this->revision;
        }

        $this->revision = new Entity\Revision();
        $this->revision->setCreatedAt(new DateTime());
        $this->revision->setComment($this->getRevisionComment()->getComment());
        $this->getRevisionComment()->setComment(null);  // Reset comment afer use

        if ($this->getAuthenticationService()->hasIdentity()) {
            $user = $this->getAuthenticationService()->getIdentity();
            $this->revision->setUserId($user->getId());
        }

        $this->getAuditObjectManager()->persist($this->revision);

        return $this->revision;
    }

    /**
     * Extract entity into a flat array where references become getId()
     * then hydrate the auditEntity with those values.
     * Return the array of values for identifier processing.
     */
    private function hydrateAuditEntityFromTargetEntity($auditEntity, $entity): array
    {
        $properties = array();
        $hydrator = new DoctrineHydrator($this->getObjectManager(), true);
        $auditHydrator = new DoctrineHydrator($this->getAuditObjectManager(), false);

        foreach ($hydrator->extract($entity) as $key => $value) {
            if (gettype($value) == 'object' and method_exists($value, 'getId')) {
                // Set values to getId for classes
                $value = $value->getId();
            } elseif ($value instanceof \Doctrine\ORM\PersistentCollection) {
                // If a property is an object we probably are not mapping that to
                // a field.  Do no special handing...
                continue;
            } elseif ($value instanceof \DateTime) {
                // DateTime is special and ok as-is
            } elseif (gettype($value) == 'object' and ! method_exists($value, 'getId')) {
                throw new Exception(get_class($value) . " does not have a getId function");
            }

            $properties[$key] = $value;
        }

        $auditHydrator->hydrate($properties, $auditEntity);

        return $properties;
    }

    /**
     * Create a set of audit entities for the entity
     */
    public function createAudit($entity, Entity\RevisionType $revisionType)
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
            // Entity is not audited
            return false;
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

        $auditEntityClass = $targetEntity->getAuditEntity()->getName();
        $auditEntity = new $auditEntityClass();
        $auditEntity->setRevisionEntity($revisionEntity);

        $entityProperties = $this->hydrateAuditEntityFromTargetEntity($auditEntity, $entity);

        foreach ($revisionEntity->getTargetEntity()->getIdentifier() as $identifier) {
            $revisionEntityIdentifierValue = new Entity\RevisionEntityIdentifierValue;
            $revisionEntityIdentifierValue
                ->setRevisionEntity($revisionEntity)
                ->setIdentifier($identifier)
                ->setValue($entityProperties[$identifier->getFieldName()])
                ;

           $this->getAuditObjectManager()->persist($revisionEntityIdentifierValue);
       }

        $this->getAuditObjectManager()->persist($revisionEntity);
        $this->getAuditObjectManager()->persist($auditEntity);

        return $auditEntity;
    }

    /**
     * Catch all entities and enqueue for postFlush processing or audit for deleted entities
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        foreach ($eventArgs->getEntityManager()->getUnitOfWork()->getScheduledEntityInsertions() as $entity) {
            $revisionType = $this->getAuditObjectManager()
                ->getRepository('ZF\Doctrine\Audit\Entity\RevisionType')
                ->findOneBy([
                    'name' => 'insert',
                ]);

            $this->enqueue([
                'entity' => $entity,
                'revisionType' => $revisionType,
            ]);
        }

        foreach ($eventArgs->getEntityManager()->getUnitOfWork()->getScheduledEntityUpdates() as $entity) {
            $revisionType = $this->getAuditObjectManager()
                ->getRepository('ZF\Doctrine\Audit\Entity\RevisionType')
                ->findOneBy([
                    'name' => 'update',
                ]);

            $this->enqueue([
                'entity' => $entity,
                'revisionType' => $revisionType,
            ]);
        }

        foreach ($eventArgs->getEntityManager()->getUnitOfWork()->getScheduledEntityDeletions() as $entity) {
            $revisionType = $this->getAuditObjectManager()
                ->getRepository('ZF\Doctrine\Audit\Entity\RevisionType')
                ->findOneBy([
                    'name' => 'delete',
                ]);

            // Delete entities are out of scope in postFlush so enqueue now.
            $this->createAudit($entity, $revisionType);
        }
    }

    /**
     * After a successful flush finish auditing enqueud entities
     * and flush the audit.
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        // insert | update entites will trigger key generation and must be audited after the flush
        foreach ($this->getQueue() as $entityMap) {
            $this->createAudit($entityMap['entity'], $entityMap['revisionType']);
        }

        // Reset
        $this->queue = [];
        $this->revision = null;

        // Flush revision and revisionEntities
        $this->getAuditObjectManager()->flush();
    }
}
