<?php

namespace ZF\Doctrine\Audit\Tools;

use ZF\Doctrine\Audit\Persistence\ObjectManagerAwareInterface;
use ZF\Doctrine\Audit\Persistence\ObjectManagerAwareTrait;
use Doctrine\Common\Persistence\ObjectManager;

final class TriggerTool implements
    ObjectManagerAwareInterface
{
    use ObjectManagerAwareTrait;

    public function __construct(ObjectManager $objectManager, array $config)
    {
        $this->setObjectManager($objectManager);
        $this->config = $config;
    }

    public function generate()
    {
        switch($this->getObjectManager()->getConnection()->getDatabasePlatform()->getName()) {
            case 'mysql':
                $generator = new TriggerGenerator\MySQL($this->getObjectManager(), $this->config);
                break;
            default:
                throw new Exception("Unsupported database platform: "
                    . $this->getObjectManager()->getConnection()->getDatabasePlatform()->getName());
                break;
        }

        return $generator->generate();
    }
}