<?php

namespace ZF\Doctrine\Audit\Mapping\Driver;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class MergedDriverFactory implements
    FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config')['zf-doctrine-audit'];

        $instance = new $requestedName();
        $instance->setAuditObjectManager($container->get($config['audit_object_manager']));

        return $instance;
    }
}