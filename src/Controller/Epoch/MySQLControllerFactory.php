<?php

namespace ZF\Doctrine\Audit\Controller\Epoch;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use ZF\Doctrine\Audit\AuditOptions;

final class MySQLControllerFactory implements
    FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config')['zf-doctrine-audit'];

        $instance = new $requestedName();
        $instance->setObjectManager($container->get($config['target_object_manager']));
        $instance->setAuditObjectManager($container->get($config['audit_object_manager']));
        $instance->setAuditOptions($container->get(AuditOptions::class));

        $instance->viewRenderer = $container->get('ViewRenderer');

        return $instance;
    }
}