<?php

namespace ZF\Doctrine\Audit\Controller;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class SchemaToolControllerFactory implements
    FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config')['zf-doctrine-audit'];

        $console = $container->get('console');
        $auditObjectManager = $container->get($config['audit_object_manager']);

        return new $requestedName($console, $auditObjectManager);
    }
}