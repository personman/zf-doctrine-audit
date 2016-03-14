<?php

namespace ZF\Doctrine\Audit\Service;

use Zend\View\Helper\AbstractHelper;
use ZF\Doctrine\Audit\Entity\AbstractAudit;
use Doctrine\Common\Persistence\Mapping\MappingException;
use ZF\Doctrine\Audit\Entity\RevisionEntity;
use ZF\Doctrine\Audit\Persistence;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class AuditService extends AbstractHelper implements
    Persistence\AuditEntitiesAwareInterface,
    Persistence\ObjectManagerAwareInterface,
    Persistence\AuditObjectManagerAwareInterface
{
    use Persistence\AuditEntitiesAwareTrait;
    use Persistence\ObjectManagerAwareTrait;
    use Persistence\AuditObjectManagerAwareTrait;

    protected $comment = '';

    /**
     * To add a comment to a revision fetch this object before flushing
     * and set the comment.  The comment will be fetched by the revision
     * and reset after reading
     */
    public function getComment(): string
    {
        $comment = $this->comment;
        $this->comment = '';

        return $comment;
    }

    public function setComment(string $comment)
    {
        $this->comment = $comment;

        return $this;
    }

    public function getEntityValues($entity)
    {
        $return = [];

        $metadata = $this->getObjectManager()->getClassMetadata(get_class($entity));
        $fields = $metadata->getFieldNames();

        foreach ($fields as $fieldName) {
            $return[$fieldName] = $metadata->getFieldValue($entity, $fieldName);
        }

        ksort($return);

        return $return;
    }

    /**
     * Extract entity into a flat array where references become getId()
     * then hydrate the auditEntity with those values
     */
    public function hydrateAuditEntityFromTargetEntity($auditEntity, $entity)
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
    }

    public function getEntityAssociations(AbstractAudit $entity)
    {
        $associations = array();
        foreach ($entity->getAssociationMappings() as $mapping) {
            $associations[$mapping['fieldName']] = $mapping;
        }

        return $associations;
    }

    /**
     * Find a mapping to the given field for 1:many
     */
    public function getAssociationRevisionEntity(AbstractAudit $entity, string $field, $value)
    {
        foreach ($entity->getAssociationMappings() as $mapping) {

            if ($mapping['fieldName'] == $field) {
                $queryBuilder = $this->getAuditObjectManager()->createQueryBuilder();
                $queryBuilder->select('revisionEntity')
                    ->from('ZF\Doctrine\Audit\\Entity\\RevisionEntity', 'revisionEntity')
                    ->innerJoin('revisionEntity.revision', 'revision')
                    ->andWhere('revisionEntity.targetEntityClass = ?1')
                    ->andWhere('revisionEntity.entityKeys = ?2')
                    ->andWhere('revision.timestamp <= ?3')
                    ->setParameter(1, $mapping['targetEntity'])
                    ->setParameter(2, json_encode(array('id' => $value), JSON_NUMERIC_CHECK))
                    ->setParameter(3, $entity->getRevisionEntity()->getRevision()->getTimestamp())
                    ->orderBy('revision.timestamp', 'DESC')
                    ->setMaxResults(1);

                $result = $queryBuilder->getQuery()->getResult();

                if ($result) {
                    return reset($result);
                }
            }
        }
    }

    public function getEntityIdentifierValues($entity, $cleanRevisionEntity = false)
    {
        try {
            // Try orm_default first
            // Get entity metadata - Audited entities will always have composite keys
            $metadataFactory = $this->getObjectManager()->getMetadataFactory();
            $metadata = $metadataFactory->getMetadataFor(get_class($entity));
        } catch (MappingException $e) {
            // Try audit
            // Get entity metadata - Audited entities will always have composite keys
            $metadataFactory = $this->getAuditObjectManager()->getMetadataFactory();
            $metadata = $metadataFactory->getMetadataFor(get_class($entity));
        }

        $values = $metadata->getIdentifierValues($entity);

        if ($cleanRevisionEntity and $values['revisionEntity'] instanceof RevisionEntity) {
            unset($values['revisionEntity']);
        }

        foreach ($values as $key => $val) {
            if (gettype($val) == 'object') {
                $values[$key] = $val->getId();
            }
        }

        // All keys are handled as strings for array serialization
        foreach ($values as $key => $val) {
            $values[$key] = $val;
        }

        return $values;
    }

    /**
     * Pass an audited entity or the audit entity
     * and return a collection of RevisionEntity s
     * for that record
     */
    public function getRevisionEntities($entity)
    {
        if (gettype($entity) != 'string' && in_array(get_class($entity), array_keys($this->getAuditEntities()))) {
            $auditEntityClass = 'ZF\\Doctrine\\Audit\\Entity\\' . str_replace('\\', '_', get_class($entity));
            $identifiers = $this->getEntityIdentifierValues($entity);
        } elseif ($entity instanceof AbstractAudit) {
            $auditEntityClass = get_class($entity);
            $identifiers = $this->getEntityIdentifierValues($entity, true);
        } else {
            $auditEntityClass = 'ZF\\Doctrine\\Audit\\Entity\\' . str_replace('\\', '_', $entity);
        }

        $search = array('auditEntityClass' => $auditEntityClass);
        if (isset($identifiers)) {
            $search['entityKeys'] = json_encode($identifiers, JSON_NUMERIC_CHECK);
        }

        return $this->getAuditObjectManager()
            ->getRepository('ZF\\Doctrine\\Audit\\Entity\\RevisionEntity')
            ->findBy(
                $search,
                array('id' => 'DESC')
            );
    }

    public function getAuditEntity(RevisionEntity $entity)
    {
        return $this->getAuditObjectManager()
            ->getRepository(
                $entity->getAuditEntityClass()
            )->findOneBy(array('revisionEntity' => $entity));
    }

    public function getTargetEntity()
    {
        return $this->getObjectManager()->getRepository(
            $this->getObjectManager()
                ->getRepository($this->getAuditEntityClass())
                ->findOneBy($this->getEntityKeys())->getAuditedEntityClass()
        )->findOneBy($this->getEntityKeys());
    }
}
