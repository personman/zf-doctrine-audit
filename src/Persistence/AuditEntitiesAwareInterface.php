<?php

namespace ZF\Doctrine\Audit\Persistence;

interface AuditEntitiesAwareInterface
{
    public function setAuditEntities(array $entities);
    public function getAuditEntities(): array;
}
