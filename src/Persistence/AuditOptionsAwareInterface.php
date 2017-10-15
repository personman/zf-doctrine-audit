<?php

namespace ZF\Doctrine\Audit\Persistence;

use ZF\Doctrine\Audit\AuditOptions;

interface AuditOptionsAwareInterface
{
    public function setAuditOptions(AuditOptions $options);
    public function getAuditOptions(): AuditOptions;
}
