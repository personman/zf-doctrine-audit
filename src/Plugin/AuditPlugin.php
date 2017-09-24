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

        $metadata = $this->repository->getObjectManager()->getClassMetadata(get_class($entity));
        $identifiers = $metadata->getIdentifierFieldNames();

        foreach ($identifiers as $id) {
            if (! $entity->{"get$id"}()) {
                return;
            }
        }

        $queryBuilder = $this->getAuditObjectManager()->createQueryBuilder();
        $queryBuilder
            ->select('row')
            ->from($auditEntityClass, 'row')
            ->innerJoin('row.revisionEntity', 'revisionEntity')
            ->innerJoin('revisionEntity.revision', 'revision')
            ->orderBy('revision.createdAt', 'ASC')
            ;

        foreach ($identifiers as $id) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq("row.$id", $entity->{"get$id"}()));
        }

        return new ArrayCollection($queryBuilder->getQuery()->getResult());
    }

    /**
     * @return datetime
     */
    public function getCreatedAt($entity)
    {
        $auditEntityClass = $this->getAuditObjectManager()
            ->getRepository(AuditEntity::class)
            ->generateClassName(get_class($entity))
            ;

        if (! class_exists($auditEntityClass)) {
            return;
        }

        $metadata = $this->repository->getObjectManager()->getClassMetadata(get_class($entity));
        $identifiers = $metadata->getIdentifierFieldNames();

        foreach ($identifiers as $id) {
            if (! $entity->{"get$id"}()) {
                return;
            }
        }

        $queryBuilder = $this->getAuditObjectManager()->createQueryBuilder();
        $queryBuilder
            ->select('row')
            ->from($auditEntityClass, 'row')
            ->innerJoin('row.revisionEntity', 'revisionEntity')
            ->innerJoin('revisionEntity.revision', 'revision')
            ->orderBy('revision.createdAt', 'ASC')
            ->setMaxResults(1)
            ;

        foreach ($identifiers as $id) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq("row.$id", $entity->{"get$id"}()));
        }

        $oldest = $queryBuilder->getQuery()->getOneOrNullResult();

        // @codeCoverageIgnoreStart
        if (! $oldest) {
            return;
        }
        // @codeCoverageIgnoreEnd

        return $oldest->getRevisionEntity()->getRevision()->getCreatedAt();
    }
}
