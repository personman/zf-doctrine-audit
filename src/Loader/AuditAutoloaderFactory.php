<?php

namespace ZF\Doctrine\Audit\Loader;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class AuditAutoloaderFactory implements
    FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config')['zf-doctrine-audit'];

        $instance = new $requestedName();
        $instance->setAuditEntities($config['entities']);
        $instance->setObjectManager($container->get($config['target_object_manager']));
        $instance->setAuditObjectManager($container->get($config['audit_object_manager']));

        return $instance;
    }
}