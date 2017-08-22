<?php

namespace ZF\Doctrine\Audit\Persistence;

use Doctrine\Common\Persistence\ObjectManager;
use ZF\Doctrine\Audit\AuditOptions;

trait AuditOptionsAwareTrait
{
    protected $auditOptions;

    public function setAuditOptions(AuditOptions $auditOptions)
    {
        $this->auditOptions = $auditOptions;

        return $this;
    }

    public function getAuditOptions(): AuditOptions
    {
        return $this->auditOptions;
    }
}
