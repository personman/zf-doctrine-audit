<?php

namespace ZF\Doctrine\Audit\Plugin;

use ZF\Doctrine\Repository\Plugin\PluginInterface;

class AuditPlugin implements PluginInterface
{
    protected $repository;
    protected $parameters;

    public function __construct(array $creationOptions)
    {
        $this->repository = $creationOptions['repository'];
        $this->parameters = $creationOptions['parameters'];
    }

    public function getCreatedAt($entity)
    {
        die('hit plugin get created at');
    }
}