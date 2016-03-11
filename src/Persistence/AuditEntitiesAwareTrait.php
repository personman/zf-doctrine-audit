<?php

namespace ZF\Doctrine\Audit\Persistence;

use Doctrine\Common\Persistence\ObjectManager;

trait AuditEntitiesAwareTrait
{
    protected $auditEntities;

    public function setAuditEntities(array $entities)
    {
        $this->auditEntities = $entities;

        return $this;
    }

    public function getAuditEntities()
    {
        return $this->auditEntities;
    }
}
