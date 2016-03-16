<?php

namespace ZFTest\Doctrine\Audit;

use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Console\Adapter\AdapterInterface as Console;
use DoctrineORMModule\Service\EntityManagerFactory;
use DoctrineORMModule\Service\DBALConnectionFactory;
use DoctrineORMModule\Service\ConfigurationFactory;
use DoctrineModule\Service\EventManagerFactory;

class Module implements
    ConfigProviderInterface
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'doctrine.entitymanager.orm_zf_doctrine_audit' 
                    => new EntityManagerFactory('orm_zf_doctrine_audit'),
                'doctrine.connection.orm_zf_doctrine_audit' 
                    => new DBALConnectionFactory('orm_zf_doctrine_audit'),
                'doctrine.configuration.orm_zf_doctrine_audit' 
                    => new ConfigurationFactory('orm_zf_doctrine_audit'),
                'doctrine.eventmanager.orm_zf_doctrine_audit' 
                    => new EventManagerFactory('orm_zf_doctrine_audit'),
            ),
        );
    }
}
