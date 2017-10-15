<?php

namespace ZF\Doctrine\Audit\Repository;

use Doctrine\ORM\EntityRepository;

class AuditEntityRepository extends EntityRepository
{
    public function generateClassName($entityName)
    {
        return "ZF\\Doctrine\\Audit\\AuditEntity\\" . str_replace('\\', '_', $entityName);
    }
}
