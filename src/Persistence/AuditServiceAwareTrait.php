<?php

namespace ZF\Doctrine\Audit\Persistence;

use ZF\Doctrine\Audit\Service\AuditService;

trait AuditServiceAwareTrait
{
    protected $auditService;

    public function setAuditService(AuditService $auditService)
    {
        $this->auditService = $auditService;

        return $this;
    }

    public function getAuditService()
    {
        return $this->auditService;
    }
}
