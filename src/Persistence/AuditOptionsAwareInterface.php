<?php

namespace ZF\Doctrine\Audit\Persistence;

interface AuditOptionsAwareInterface
{
    public function setAuditOptions(array $options);
    public function getAuditOptions();
}
