<?php

namespace ZF\Doctrine\Audit\View\Helper;

use Zend\View\Helper\AbstractHelper;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;
use ZF\Doctrine\Audit\Entity\AbstractAudit;
use ZF\Doctrine\Audit\Persistence;

final class RevisionEntityPaginator extends AbstractHelper implements
    Persistence\AuditObjectManagerAwareInterface,
    Persistence\AuditServiceAwareInterface,
    Persistence\AuditEntitiesAwareInterface,
    Persistence\AuditOptionsAwareInterface
{
    use Persistence\AuditObjectManagerAwareTrait;
    use Persistence\AuditServiceAwareTrait;
    use Persistence\AuditEntitiesAwareTrait;
    use Persistence\AuditOptionsAwareTrait;

    public function __invoke($page, $entity)
    {
        if (gettype($entity) != 'string' and in_array(get_class($entity), array_keys($this->getAuditEntities()))) {
            $auditEntityClass = 'ZF\Doctrine\Audit\\Entity\\' . str_replace('\\', '_', get_class($entity));
            $identifiers = $this->getAuditService()->getEntityIdentifierValues($entity);
        } elseif ($entity instanceof AbstractAudit) {
            $auditEntityClass = get_class($entity);
            $identifiers = $this->getAuditService()->getEntityIdentifierValues($entity, true);
        } else {
            $auditEntityClass = 'ZF\Doctrine\Audit\\Entity\\' . str_replace('\\', '_', $entity);
        }

        $search = array('auditEntityClass' => $auditEntityClass);
        if (isset($identifiers)) {
            $search['entityKeys'] = json_encode($identifiers, JSON_NUMERIC_CHECK);
        }

        $queryBuilder = $this->getAuditObjectManager()
            ->getRepository('ZF\Doctrine\Audit\\Entity\\RevisionEntity')
            ->createQueryBuilder('rev');

        $queryBuilder->orderBy('rev.id', 'DESC');

        $i = 0;
        foreach ($search as $key => $val) {
            $i ++;
            $queryBuilder->andWhere("rev.$key = ?$i");
            $queryBuilder->setParameter($i, $val);
        }

        $adapter = new DoctrineAdapter(new ORMPaginator($queryBuilder));
        $paginator = new Paginator($adapter);

        $paginator->setDefaultItemCountPerPage($this->getAuditOptions()['paginator_limit']);
        $paginator->setCurrentPageNumber($page);

        return $paginator;
    }
}
