<?php

namespace ZF\Doctrine\Audit\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Model\ViewModel;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;
use ZF\Doctrine\Audit\Entity\AbstractAudit;
use ZF\Doctrine\Audit\Persistence;

final class OneToManyPaginator extends AbstractHelper implements
    Persistence\AuditObjectManagerAwareInterface,
    Persistence\AuditOptionsAwareInterface
{
    use Persistence\AuditObjectManagerAwareTrait;
    use Persistence\AuditOptionsAwareTrait;

    public function __invoke($page, $revisionEntity, $joinTable, $mappedBy)
    {
        $entityClassName = 'ZF\\Doctrine\\Audit\\Entity\\' . str_replace('\\', '_', $joinTable);

        $query = $this->getAuditObjectManager()->createQuery(
            "
            SELECT e
            FROM ZF\Doctrine\Audit\Entity\RevisionEntity e
            JOIN e.revision r
            WHERE e.id IN (
                SELECT re.id
                FROM $entityClassName s
                JOIN s.revisionEntity re
                WHERE s.$mappedBy = :var
            )
            ORDER BY r.timestamp DESC
        "
        );
        $query->setParameter('var', $revisionEntity->getTargetEntity());

        $adapter = new DoctrineAdapter(new ORMPaginator($query));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage($this->getAuditOptions()['paginator_limit']);
        $paginator->setCurrentPageNumber($page);

        return $paginator;
    }
}
