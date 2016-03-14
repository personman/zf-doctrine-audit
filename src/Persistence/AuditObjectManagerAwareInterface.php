<?php

namespace ZF\Doctrine\Audit\Persistence;

use Doctrine\Common\Persistence\ObjectManager;

interface AuditObjectManagerAwareInterface
{
    public function setAuditObjectManager(ObjectManager $objectManager);
    public function getAuditObjectManager(): ObjectManager;
}
