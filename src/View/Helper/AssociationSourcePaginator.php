<?php

namespace ZF\Doctrine\Audit\View\Helper;

use Zend\View\Helper\AbstractHelper;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;
use ZF\Doctrine\Audit\Persistence;

final class AssociationSourcePaginator extends AbstractHelper implements
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

        $queryBuilder = $repository->createQueryBuilder('association');
        $queryBuilder->andWhere('association.sourceRevisionEntity = :var');
        $queryBuilder->setParameter('var', $revisionEntity);

        $adapter = new DoctrineAdapter(new ORMPaginator($queryBuilder));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage($this->getAuditOptions()['paginator_limit']);

        $paginator->setCurrentPageNumber($page);

        return $paginator;
    }
}
