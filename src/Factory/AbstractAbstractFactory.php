<?php

namespace ZF\Doctrine\Audit\Factory;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\AbstractFactoryInterface;
use ZF\Doctrine\Audit\Persistence;

/**
 * This module uses many classes which implement the same interfaces.
 * Instead of assigning instantiators for Controllers, View Plugins,
 * and the Global Service Manager, all classes in this module are
 * instead created through this abstract factory.
 */

abstract class AbstractAbstractFactory implements
    AbstractFactoryInterface
{
    protected $factoryClasses;
    protected $initializers;

    public function getInitializers(): array
    {
        return $this->initializers ?? $this->initializers = [
            new Persistence\ObjectManagerInitializer(),
            new Persistence\AuditObjectManagerInitializer(),
            new Persistence\AuditServiceInitializer(),
            new Persistence\AuditOptionsInitializer(),
            new Persistence\AuditEntitiesInitializer(),
            new Persistence\AuthenticationServiceInitializer(),
        ];
    }

    public function canCreateServiceWithName(
        ServiceLocatorInterface $serviceLocator,
        string $name,
        string $requestedName
    ): boolean {

        return in_array($requestedName, array_keys($this->factoryClasses));
    }

    public function createServiceWithName(
        ServiceLocatorInterface $serviceLocator,
        string $name,
        string $requestedName
    ) {

        $instance = new $this->factoryClasses[$requestedName]();

        if (method_exists($serviceLocator, 'getServiceLocator')) {
            $serviceLocator = $serviceLocator->getServiceLocator();
        }

        foreach ($this->getInitializers() as $initializer) {
            $initializer->initialize($instance, $serviceLocator);
        }

        return $instance;
    }
}
