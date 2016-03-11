<?php

namespace ZF\Doctrine\Audit\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;
use ZF\Doctrine\Audit\Persistence;

final class EntityOptions extends AbstractHelper implements
    Persistence\AuditEntitiesAwareInterface
{
    use Persistence\AuditEntitiesAwareTrait;

    public function __invoke($entityClass = null)
    {
        if ($entityClass) {
            return $this->getAuditEntities()[$entityClass];
        }

        return $this->getAuditEntities();
    }
}
