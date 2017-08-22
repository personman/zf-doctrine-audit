<?php

namespace ZF\Doctrine\Audit\Persistence;

use Doctrine\Common\Persistence\ObjectManager;

trait AuditObjectManagerAwareTrait
{
    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $auditObjectManager;

    public function setAuditObjectManager(ObjectManager $auditObjectManager)
    {
        $this->auditObjectManager = $auditObjectManager;

        return $this;
    }

    /**
     * @return Doctrine\ORM\EntityManager
     */
    public function getAuditObjectManager(): ObjectManager
    {
        return $this->auditObjectManager;
    }
}
