<?php

namespace ZF\Doctrine\Audit\Mapping\Driver;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use ZF\Doctrine\Audit\AuditOptions;

class JoinEntityDriverFactory implements
    FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config')['zf-doctrine-audit'];

        $instance = new $requestedName();
        $instance->setJoinEntityConfigCollection(new ArrayCollection($config['joinEntities']));
        $instance->setObjectManager($container->get($config['target_object_manager']));
        $instance->setAuditObjectManager($container->get($config['audit_object_manager']));
        $instance->setAuditOptions($container->get(AuditOptions::class));

        return $instance;
    }
}