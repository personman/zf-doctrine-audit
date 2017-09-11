<?php

namespace ZF\Doctrine\Audit\Tools;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use ZF\Doctrine\Audit\AuditOptions;

class EpochToolFactory implements
    FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config')['zf-doctrine-audit'];

        $objectManager = $container->get($config['target_object_manager']);
        $auditObjectManager = $container->get($config['audit_object_manager']);
        $auditOptions = $container->get(AuditOptions::class);
        $viewRenderer = $container->get('ViewRenderer');

        return new $requestedName($objectManager, $auditObjectManager, $auditOptions, $viewRenderer);
    }
}