<?php

namespace ZF\Doctrine\Audit\View\Helper;

use Zend\View\Helper\AbstractHelper;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;
use ZF\Doctrine\Audit\Entity\Persistence;

final class AssociationTargetPaginator extends AbstractHelper implements
    Persistence\AuditObjectManagerAwareInterface,
    Persistence\AuditServiceAwareInterface,
    Persistence\AuditOptionsAwareInterface
{
    use Persistence\AuditObjectManagerAwareTrait;
    use Persistence\AuditServiceAwareTrait;
    use Persistence\AuditOptionsAwareTrait;

    public function __invoke($page, $revisionEntity, $joinTable)
    {
        foreach ($this->getAuditService()
            ->getEntityAssociations($revisionEntity->getAuditEntity()) as $field => $value) {
            if (isset($value['joinTable']['name']) and $value['joinTable']['name'] == $joinTable) {
                $mapping = $value;
                break;
            }
        }

        $repository = $this->getAuditObjectManager()
            ->getRepository('ZF\Doctrine\Audit\\Entity\\' . str_replace('\\', '_', $joinTable));

        $qb = $repository->createQueryBuilder('association');
        $qb->andWhere('association.targetRevisionEntity = :var');
        $qb->setParameter('var', $revisionEntity);

        $adapter = new DoctrineAdapter(new ORMPaginator($qb));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage($this->getAuditOptions()['paginator_limit']);

        $paginator->setCurrentPageNumber($page);

        return $paginator;
    }
}
