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
use ZF\Doctrine\Audit\Options\ModuleOptions;
use ZF\Doctrine\Audit\Service\AuditService;
use ZF\Doctrine\Audit\View\Helper\DateTimeFormatter;
use ZF\Doctrine\Audit\EventListener\LogRevision;
use ZF\Doctrine\Audit\Mapping\Driver\AuditDriver;

class Module implements
    ConfigProviderInterface,
    ConsoleUsageProviderInterface,
    ServiceProviderInterface
{
    private static $moduleOptions;

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

            'ZF\Doctrine\Audit\Loader\AuditAutoloader' => array(
                'namespaces' => array(
                    'ZF\Doctrine\Audit\Entity' => __DIR__,
                )
            ),
        );
    }

    public static function setModuleOptions(ModuleOptions $moduleOptions)
    {
        self::$moduleOptions = $moduleOptions;
    }

    public static function getModuleOptions()
    {
        return self::$moduleOptions;
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap(MvcEvent $e)
    {
        $serviceManager = $e->getParam('application')->getServiceManager();
        $objectManager = $serviceManager->get('doctrine.entitymanager.orm_default');
        $auditObjectManager = $serviceManager->get('doctrine.entitymanager.orm_zf_doctrine_audit');

        self::setModuleOptions($serviceManager->get('auditModuleOptions'));

        // Subscribe all event listeners in the manager
        $logRevisionEventSubscriber = $serviceManager->get('ZF\Doctrine\Audit\EventListener\LogRevision');
        $objectManager->getEventManager()->addEventSubscriber($logRevisionEventSubscriber);

        // Add audit driver
        $auditDriver = $serviceManager->get('ZF\Doctrine\Audit\Mapping\Driver\AuditDriver');
        $auditObjectManager->getConfiguration()->getMetadataDriverImpl()
            ->addDriver($auditDriver, 'ZF\Doctrine\Audit\Entity');
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'ZF\Doctrine\Audit\EventListener\LogRevision' => function(ServiceManager $serviceManager) {
                    $eventSubscriber = new LogRevision();
                    $auditObjectManager = $serviceManager->get('doctrine.entitymanager.orm_zf_doctrine_audit');
                    $config = $serviceManager->get('Config')['zf-doctrine-audit'];

                    $eventSubscriber->setAuditObjectManager($auditObjectManager);
                    $eventSubscriber->setAuditedEntities(array_keys($config['entities']));
                    $eventSubscriber->setAuthenticationService($serviceManager->get($config['authenticationService']));
                    $eventSubscriber->setAuditService($serviceManager->get('ZF\Doctrine\Audit\Service\AuditService'));

                    return $eventSubscriber;
                },

                'ZF\Doctrine\Audit\Mapping\Driver\AuditDriver' => function(ServiceManager $serviceManager) {
                    $config = $serviceManager->get('Config')['zf-doctrine-audit'];

                    $auditDriver = new AuditDriver();
                    $auditDriver->setAuditedEntities(array_keys($config['entities']));
                    $auditObjectManager = $serviceManager->get('doctrine.entitymanager.orm_zf_doctrine_audit');
                    $objectManager = $serviceManager->get('doctrine.entitymanager.orm_default');
                    $auditDriver->setObjectManager($objectManager);
                    $auditDriver->setAuditObjectManager($auditObjectManager);

                    return $auditDriver;
                },

                'ZF\Doctrine\Audit\Service\AuditService' => function(ServiceManager $serviceManager) {
                    return new AuditService();
                },

                'doctrine.entitymanager.orm_zf_doctrine_audit' => new EntityManagerFactory('orm_zf_doctrine_audit'),
                'doctrine.connection.orm_zf_doctrine_audit' => new DBALConnectionFactory('orm_zf_doctrine_audit'),
                'doctrine.configuration.orm_zf_doctrine_audit' => new ConfigurationFactory('orm_zf_doctrine_audit'),
                'doctrine.eventmanager.orm_zf_doctrine_audit' => new EventManagerFactory('orm_zf_doctrine_audit'),

                'auditModuleOptions' => function($serviceManager) {
                    $config = $serviceManager->get('Application')->getConfig();
                    $auditConfig = new ModuleOptions();
                    $auditConfig->setDefaults($config['zf-doctrine-audit']);
                    $auditConfig->setObjectManager($serviceManager->get('doctrine.entitymanager.orm_default'));
                    $auditConfig->setAuditObjectManager($serviceManager->get('doctrine.entitymanager.orm_zf_doctrine_audit'));
                    $auditConfig->setAuditService($serviceManager->get('ZF\Doctrine\Audit\Service\AuditService'));

                    $auth = $serviceManager->get($auditConfig->getAuthenticationService());
                    if ($auth->hasIdentity()) {
                        if ($auditConfig->getObjectManager()->contains($auth->getIdentity())) {
                            $auditConfig->setUser($auth->getIdentity());
                        } else {
                            $auditConfig->setUser($auditConfig->getObjectManager()->merge($auth->getIdentity()));
                        }
                    }

                    return $auditConfig;
                },
            ),
        );
    }

    public function getViewHelperConfig()
    {
         return array(
            'factories' => array(
                'auditDateTimeFormatter' => function(ServiceManager $serviceManager) {
                    $format = $serviceManager->getServiceLocator()
                        ->get('Config')['zf-doctrine-audit']['datetimeFormat'];
                    $formatter = new DateTimeFormatter();

                    return $formatter->setDateTimeFormat($format);
                },

                'auditService' => function(ServiceManager $serviceManager) {
                    return new AuditService();
                }
            )
        );
    }
}
