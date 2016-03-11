<?php

namespace ZF\Doctrine\Audit\Persistence;

use ZF\Doctrine\Audit\Service\AuditService;

interface AuditServiceAwareInterface
{
    public function setAuditService(AuditService $auditService);
    public function getAuditService();
}
