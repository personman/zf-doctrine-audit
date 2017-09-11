<?php

namespace ZF\Doctrine\Audit\Tools;

use ZF\Doctrine\Audit\Persistence\AuditObjectManagerAwareInterface;
use ZF\Doctrine\Audit\Persistence\AuditObjectManagerAwareTrait;
use ZF\Doctrine\Audit\Persistence\ObjectManagerAwareInterface;
use ZF\Doctrine\Audit\Persistence\ObjectManagerAwareTrait;
use Doctrine\Common\Persistence\ObjectManager;

final class TriggerTool implements
    AuditObjectManagerAwareInterface,
    ObjectManagerAwareInterface
{
    use AuditObjectManagerAwareTrait;
    use ObjectManagerAwareTrait;

    public function __construct(ObjectManager $objectManager, ObjectManager $auditObjectManager, array $config)
    {
        $this->setObjectManager($objectManager);
        $this->setAuditObjectManager($auditObjectManager);
        $this->config = $config;
    }

    public function generate()
    {
        switch($this->getObjectManager()->getConnection()->getDatabasePlatform()->getName()) {
            case 'mysql':
                $generator = new Generator\Trigger\MySQL($this->getObjectManager(), $this->getAuditObjectManager(), $this->config);
                break;
            default:
                throw new Exception("Unsupported database platform: "
                    . $this->getObjectManager()->getConnection()->getDatabasePlatform()->getName());
                break;
        }

        return $generator->generate();
    }
}