<?php

namespace ZF\Doctrine\Audit\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Doctrine\ORM\EntityManager;
use Zend\View\Model\ViewModel;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;
use ZF\Doctrine\Audit\Entity\AbstractAudit;
use ZF\Doctrine\Audit\Persistence;

final class RevisionPaginator extends AbstractHelper implements
    Persistence\AuditOptionsAwareInterface,
    Persistence\AuditObjectManagerAwareInterface
{
    use Persistence\AuditOptionsAwareTrait;
    use Persistence\AuditObjectManagerAwareTrait;

    public function __invoke($page, $filter = array())
    {
        $repository = $this->getAuditObjectManager()
            ->getRepository('ZF\\Doctrine\\Audit\\Entity\\Revision');

        $qb = $repository->createQueryBuilder('revision');
        $qb->orderBy('revision.id', 'DESC');

        $i = 0;
        foreach ($filter as $field => $value) {
            if (!is_null($value)) {
                $qb->andWhere("revision.$field = ?$i");
                $qb->setParameter($i, $value);
            } else {
                $qb->andWhere("revision.$field is NULL");
            }
        }

        $adapter = new DoctrineAdapter(new ORMPaginator($qb));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage($this->getAuditOptions()['paginator_limit']);

        $paginator->setCurrentPageNumber($page);

        return $paginator;
    }
}
