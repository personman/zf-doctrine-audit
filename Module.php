<?php

namespace ZF\Doctrine\Audit;

use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\ServiceManager\ServiceManager;

class Module implements
    ConfigProviderInterface,
    ConsoleUsageProviderInterface
{
    public function getConsoleUsage(Console $console)
    {
        return array(
            'zf-doctrine-audit:schema-tool:update' => 'Get update SQL for audit',
            'zf-doctrine-audit:data-fixture:import' => 'Create audit entity fixtures. '
                . 'Run before target entity fixtures.',
            'zf-doctrine-audit:epoch:import --mysql' => 'Create epoch stored procedures',
            'zf-doctrine-audit:field:activate --entity="entity\name" --field="fieldName" [--comment=]' =>
                'Activate a field for auditing',
            'zf-doctrine-audit:field:deactivate --entity="entity\name" --field="fieldName" [--comment=]' =>
                'Deactivate a field from auditing',
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
        $serviceManager->get('ZF\Doctrine\Audit\Mapping\Driver\AuditDriver')->register();
        $serviceManager->get('ZF\Doctrine\Audit\EventListener\LogRevision')->register();
    }
}
