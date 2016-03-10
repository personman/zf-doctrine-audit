<?php

namespace ZF\Doctrine\Audit;

use Zend\Mvc\MvcEvent;
use ZF\Doctrine\Audit\Options\ModuleOptions;
use ZF\Doctrine\Audit\Service\AuditService;
use ZF\Doctrine\Audit\Loader\AuditAutoloader;
use ZF\Doctrine\Audit\EventListener\LogRevision;
use ZF\Doctrine\Audit\View\Helper\DateTimeFormatter;
use ZF\Doctrine\Audit\View\Helper\EntityValues;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\ModuleManager\Feature\ServiceProviderInterface;

use DoctrineORMModule\Service\EntityManagerFactory;
use DoctrineORMModule\Service\DBALConnectionFactory;
use DoctrineORMModule\Service\ConfigurationFactory;
use DoctrineModule\Service\EventManagerFactory;

class Module implements
    ConfigProviderInterface,
    ConsoleUsageProviderInterface
{
    public function getConsoleUsage(Console $console)
    {
        return array(
            'zf-doctrine-audit:schema-tool:update' => 'Get Update SQL',
        );
    }

    private static $moduleOptions;

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

    public function onBootstrap(MvcEvent $e)
    {
        $moduleOptions = $e->getApplication()
            ->getServiceManager()
            ->get('auditModuleOptions')
            ;

        self::setModuleOptions($moduleOptions);
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

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'doctrine.entitymanager.orm_zf_doctrine_audit' => new EntityManagerFactory('orm_zf_doctrine_audit'),
                'doctrine.connection.orm_zf_doctrine_audit' => new DBALConnectionFactory('orm_zf_doctrine_audit'),
                'doctrine.configuration.orm_zf_doctrine_audit' => new ConfigurationFactory('orm_zf_doctrine_audit'),
                'doctrine.eventmanager.orm_zf_doctrine_audit' => new EventManagerFactory('orm_zf_doctrine_audit'),
                'auditModuleOptions' => function($serviceManager) {
                    $config = $serviceManager->get('Application')->getConfig();
                    $auditConfig = new ModuleOptions();
                    $auditConfig->setDefaults($config['audit']);
                    $auditConfig->setObjectManager($serviceManager->get('doctrine.entitymanager.orm_default'));
                    $auditConfig->setAuditObjectManager($serviceManager->get('doctrine.entitymanager.orm_zf_doctrine_audit'));
                    $auditConfig->setAuditService($serviceManager->get('auditService'));

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

                'auditService' => function($sm) {
                    return new AuditService();
                }
            ),
        );
    }

    public function getViewHelperConfig()
    {
         return array(
            'factories' => array(
                'auditDateTimeFormatter' => function($sm) {
                    $format = $sm->getServiceLocator()->get("Config")['audit']['datetimeFormat'];
                    $formatter = new DateTimeFormatter();

                    return $formatter->setDateTimeFormat($format);
                },

                'auditService' => function($sm) {
                    return new AuditService();
                }
            )
        );
    }
}
