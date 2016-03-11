<?php

namespace ZF\Doctrine\Audit\Persistence;

use Doctrine\Common\Persistence\ObjectManager;

trait AuditOptionsAwareTrait
{
    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $auditOptions;

    public function setAuditOptions(array $options)
    {
        $this->auditOptions = $options;
    }

    /**
     * @return Doctrine\ORM\EntityManager
     */
    public function getAuditOptions()
    {
        return $this->auditOptions;
    }
}
