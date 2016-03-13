<?php

namespace ZF\Doctrine\Audit\Factory;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\AbstractFactoryInterface;
use ZF\Doctrine\Audit\Persistence;

/**
 * This module uses many classes which implement the same interfaces.
 * Instead of assigning instantiators for Controllers, View Plugins,
 * and the Global Service Manager, all classes in this module are
 * instead created by this abstract factory.
 */

class ZFDoctrineAuditAbstractFactory implements
    AbstractFactoryInterface
{
    protected $factoryClasses = [
        'ZF\Doctrine\Audit\EventListener\LogRevision',
        'ZF\Doctrine\Audit\Mapping\Driver\AuditDriver',
        'ZF\Doctrine\Audit\Service\AuditService',
        'ZF\Doctrine\Audit\Loader\AuditAutoloader',
        'ZF\Doctrine\Audit\Controller\IndexController',
        'ZF\Doctrine\Audit\Controller\SchemaToolController',
        'ZF\Doctrine\Audit\Controller\EpochController',
        'ZF\Doctrine\Audit\View\Helper\CurrentRevisionEntity',
        'ZF\Doctrine\Audit\View\Helper\EntityOptions',
        'ZF\Doctrine\Audit\View\Helper\RevisionEntityLink',
        'ZF\Doctrine\Audit\View\Helper\RevisionPaginator',
        'ZF\Doctrine\Audit\View\Helper\RevisionEntityPaginator',
        'ZF\Doctrine\Audit\View\Helper\AssociationSourcePaginator',
        'ZF\Doctrine\Audit\View\Helper\AssociationTargetPaginator',
        'ZF\Doctrine\Audit\View\Helper\OneToManyPaginator',
        'ZF\Doctrine\Audit\View\Helper\DateTimeFormatter',
        'ZF\Doctrine\Audit\Service\AuditService',
    ];

    protected $initializers;

    public function createInitializers()
    {
        if ($this->initializers) {
            return;
        }

        $this->initializers[] = new Persistence\ObjectManagerInitializer();
        $this->initializers[] = new Persistence\AuditObjectManagerInitializer();
        $this->initializers[] = new Persistence\AuditServiceInitializer();
        $this->initializers[] = new Persistence\AuditOptionsInitializer();
        $this->initializers[] = new Persistence\AuditEntitiesInitializer();
        $this->initializers[] = new Persistence\AuthenticationServiceInitializer();
    }

    public function canCreateServiceWithName(
        ServiceLocatorInterface $serviceLocator,
        $name,
        $requestedName)
    {
        return in_array($requestedName, $this->factoryClasses);
    }

    public function createServiceWithName(
        ServiceLocatorInterface $serviceLocator,
        $name,
        $requestedName)
    {
        $this->createInitializers();

        $instance = new $requestedName();
        foreach ($this->initializers as $initializer) {
            $initializer->initialize($instance, $serviceLocator);
        }

        return $instance;
    }
}