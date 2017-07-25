<?php

namespace ZF\Doctrine\Audit\Tools\TriggerGenerator;

use Doctrine\Common\Persistence\ObjectManager;
use Exception;

class MySQL
{
    private $objectManager;
    private $config;

    public function __construct(ObjectManager $objectManager, array $config)
    {
        $this->objectManager = $objectManager;
        $this->config = $config;

        if ($this->objectManager->getConnection()->getDatabasePlatform()->getName() !== 'mysql')
        {
            throw new Exception('Invalid database platform for MySQL trigger generator');
        }
    }

    public function generate()
    {
        return 'trigger code!';
    }
}