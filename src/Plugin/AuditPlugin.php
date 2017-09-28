<?php

namespace ZF\Doctrine\Audit\Plugin;

use ZF\Doctrine\Repository\Plugin\PluginInterface;
use ZF\Doctrine\Audit\Persistence\AuditObjectManagerAwareInterface;
use ZF\Doctrine\Audit\Persistence\AuditObjectManagerAwareTrait;
use ZF\Doctrine\Audit\Entity\AuditEntity;
use Doctrine\Common\Collections\ArrayCollection;

class AuditPlugin implements
    PluginInterface,
    AuditObjectManagerAwareInterface
{
    use AuditObjectManagerAwareTrait;

    protected $repository;
    protected $parameters;

    public function __construct(array $creationOptions)
    {
        $this->repository = $creationOptions['repository'];
        $this->parameters = $creationOptions['parameters'];
    }

    /**
     * return ArrayCollection
     */
    public function getRevisionEntityCollection($entity)
    {
        $auditEntityClass = $this->getAuditObjectManager()
            ->getRepository(AuditEntity::class)
            ->generateClassName(get_class($entity))
            ;

        // @codeCoverageIgnoreStart
        if (! class_exists($auditEntityClass)) {
            return;
        }
        // @codeCoverageIgnoreEnd

        $identifierValues = $this->repository
            ->getObjectManager()
            ->getClassMetadata(get_class($entity))
            ->getIdentifierValues($entity);

        if (! $identifierValues) {
            return;
        }

        $queryBuilder = $this->getAuditObjectManager()->createQueryBuilder();
        $queryBuilder
            ->select('row')
            ->from($auditEntityClass, 'row')
            ->innerJoin('row.revisionEntity', 'revisionEntity')
            ->innerJoin('revisionEntity.revision', 'revision')
            ->orderBy('revision.createdAt', 'ASC')
            ;

        foreach ($identifierValues as $id => $value) {
            if (is_null($value)) {
                $queryBuilder->andWhere($queryBuilder->expr()->isnull("row.$id", $value));
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->eq("row.$id", $value));
            }
        }

        return new ArrayCollection($queryBuilder->getQuery()->getResult());
    }

    /**
     * @return datetime
     */
    private function getBoundingRevisionEntity($entity, $direction)
    {
        $auditEntityClass = $this->getAuditObjectManager()
            ->getRepository(AuditEntity::class)
            ->generateClassName(get_class($entity))
            ;

        if (! class_exists($auditEntityClass)) {
            return;
        }

        $queryBuilder = $this->getAuditObjectManager()->createQueryBuilder();
        $queryBuilder
            ->select('row')
            ->from($auditEntityClass, 'row')
            ->innerJoin('row.revisionEntity', 'revisionEntity')
            ->innerJoin('revisionEntity.revision', 'revision')
            ->orderBy('revision.createdAt', $direction)
            ->setMaxResults(1)
            ;

        $identifierValues = $this->repository
            ->getObjectManager()
            ->getClassMetadata(get_class($entity))
            ->getIdentifierValues($entity);

        if (! $identifierValues) {
            return;
        }

        foreach ($identifierValues as $id => $value) {
            if (is_null($value)) {
                $queryBuilder->andWhere($queryBuilder->expr()->isnull("row.$id", $value));
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->eq("row.$id", $value));
            }
        }

        $oldest = $queryBuilder->getQuery()->getOneOrNullResult();

        // @codeCoverageIgnoreStart
        if (! $oldest) {
            return;
        }
        // @codeCoverageIgnoreEnd

        return $oldest;
    }

    public function getNewestRevisionEntity($entity)
    {
        return $this->getBoundingRevisionEntity($entity, 'desc');
    }

    public function getOldestRevisionEntity($entity)
    {
        return $this->getBoundingRevisionEntity($entity, 'asc');
    }
}
