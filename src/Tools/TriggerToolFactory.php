<?php

namespace ZF\Doctrine\Audit\Tools;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class TriggerToolFactory implements
    FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config')['zf-doctrine-audit'];
        $objectManager = $container->get($config['target_object_manager']);
        $auditObjectManager = $container->get($config['audit_object_manager']);

        return new $requestedName($objectManager, $auditObjectManager, $config);
    }
}
