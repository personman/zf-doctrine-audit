<?php

namespace ZF\Doctrine\Audit\Plugin;

use ZF\Doctrine\Repository\Plugin\PluginInterface;
use ZF\Doctrine\Audit\Persistence\AuditObjectManagerAwareInterface;
use ZF\Doctrine\Audit\Persistence\AuditObjectManagerAwareTrait;
use ZF\Doctrine\Audit\Entity\AuditEntity;

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

    public function getCreatedAt($entity)
    {
        $auditEntityClass = $this->getAuditObjectManager()
            ->getRepository(AuditEntity::class)
            ->generateClassName(get_class($entity))
            ;

        $queryBuilder = $this->getAuditObjectManager()->createQueryBuilder();
        $queryBuilder
            ->select('row')
            ->from($auditEntityClass, 'row')
            ->innerJoin('row.revisionEntity', 'revisionEntity')
            ->innerJoin('revisionEntity.revision', 'revision')
            ->andWhere($queryBuilder->expr()->eq('row.id', $entity->getId()))
            ->orderBy('revision.createdAt', 'ASC')
            ->setMaxResults(1)
            ;

        $oldest = $queryBuilder->getQuery()->getOneOrNullResult();

        if ($oldest) {
            return $oldest->getRevisionEntity()->getRevision()->getCreatedAt();
        }
    }
}
