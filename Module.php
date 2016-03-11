<?php

namespace ZF\Doctrine\Audit;

use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\ServiceManager\ServiceManager;
use DoctrineORMModule\Service\EntityManagerFactory;
use DoctrineORMModule\Service\DBALConnectionFactory;
use DoctrineORMModule\Service\ConfigurationFactory;
use DoctrineModule\Service\EventManagerFactory;
use ZF\Doctrine\Audit\View\Helper\DateTimeFormatter;

class Module implements
    ConfigProviderInterface,
    ConsoleUsageProviderInterface,
    ServiceProviderInterface
{
    public function getConsoleUsage(Console $console)
    {
        return array(
            'zf-doctrine-audit:schema-tool:update' => 'Get Update SQL for Audit',
        );
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap(MvcEvent $e)
    {
        $serviceManager = $e->getParam('application')->getServiceManager();

        $serviceManager->get('ZF\Doctrine\Audit\Loader\AuditAutoloader')->register();
        $serviceManager->get('ZF\Doctrine\Audit\EventListener\LogRevision')->register();
        $serviceManager->get('ZF\Doctrine\Audit\Mapping\Driver\AuditDriver')->register();
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'doctrine.entitymanager.orm_zf_doctrine_audit' => new EntityManagerFactory('orm_zf_doctrine_audit'),
                'doctrine.connection.orm_zf_doctrine_audit' => new DBALConnectionFactory('orm_zf_doctrine_audit'),
                'doctrine.configuration.orm_zf_doctrine_audit' => new ConfigurationFactory('orm_zf_doctrine_audit'),
                'doctrine.eventmanager.orm_zf_doctrine_audit' => new EventManagerFactory('orm_zf_doctrine_audit'),
            ),
        );
    }

    public function getViewHelperConfig()
    {
         return array(
            'factories' => array(
                'auditDateTimeFormatter' => function(ServiceManager $serviceManager) {
                    $format = $serviceManager->getServiceLocator()
                        ->get('Config')['zf-doctrine-audit']['datetime_format'];
                    $formatter = new DateTimeFormatter();

                    return $formatter->setDateTimeFormat($format);
                },

                'auditService' => function(ServiceManager $serviceManager) {
                    return $serviceManager->getServiceLocator()->get('ZF\Doctrine\Audit\Service\AuditService');
                }
            )
        );
    }
}
