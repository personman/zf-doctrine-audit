<?php

namespace ZF\Doctrine\Audit\Persistence;

use Doctrine\Common\Persistence\ObjectManager;

trait AuditObjectManagerAwareTrait
{
    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $auditObjectManager;

    public function setAuditObjectManager(ObjectManager $objectManager)
    {
        $this->auditObjectManager = $objectManager;
    }

    /**
     * @return Doctrine\ORM\EntityManager
     */
    public function getAuditObjectManager(): ObjectManager
    {
        return $this->auditObjectManager;
    }
}
