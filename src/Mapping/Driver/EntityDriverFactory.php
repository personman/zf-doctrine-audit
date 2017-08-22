<?php

namespace ZF\Doctrine\Audit\Mapping\Driver;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use ZF\Doctrine\Audit\AuditOptions;

class EntityDriverFactory implements
    FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config')['zf-doctrine-audit'];

        $instance = new $requestedName();
        $instance->setEntityConfigCollection(new ArrayCollection($config['entities']));
        $instance->setObjectManager($container->get($config['target_object_manager']));
        $instance->setAuditObjectManager($container->get($config['audit_object_manager']));
        $instance->setAuditOptions($container->get(AuditOptions::class));

        return $instance;
    }
}