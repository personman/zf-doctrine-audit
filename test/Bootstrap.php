<?php

namespace ZFTest\Doctrine\Audit;

use Zend\Loader\AutoloaderFactory;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;
use RuntimeException;
use DoctrineDataFixtureModule\Loader\ServiceLocatorAwareLoader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Loader;
use MySQLi;
use ZF\Doctrine\Audit\Tools\TriggerTool;

error_reporting(E_ALL | E_STRICT);
chdir(__DIR__);


$localConfig = include(__DIR__ . '/autoload/tests.global.php');

foreach ($localConfig['doctrine']['connection'] as $name => $dbConfig) {
    #print_r($dbConfig['params']);die();
    $mysqli = new MySQLi($dbConfig['params']['host'], $dbConfig['params']['user'], '');
    $mysqli->query('DROP DATABASE IF EXISTS ' . $dbConfig['params']['dbname']);
    $mysqli->query('CREATE DATABASE ' . $dbConfig['params']['dbname']);
    $mysqli->close();

    echo 'created ' . $dbConfig['params']['dbname'] . "\n";
}


class Bootstrap
{
    protected static $config;

    public static function init()
    {
        // Load the user-defined test configuration file, if it exists; otherwise, load
        if (is_readable(__DIR__ . '/TestConfig.php')) {
            $testConfig = include __DIR__ . '/TestConfig.php';
        } else {
            $testConfig = include __DIR__ . '/TestConfig.php.dist';
        }

        $zf2ModulePaths = array();

        if (isset($testConfig['module_listener_options']['module_paths'])) {
            $modulePaths = $testConfig['module_listener_options']['module_paths'];
            foreach ($modulePaths as $modulePath) {
                if (($path = static::findParentPath($modulePath)) ) {
                    $zf2ModulePaths[] = $path;
                }
            }
        }

        $zf2ModulePaths  = implode(PATH_SEPARATOR, $zf2ModulePaths) . PATH_SEPARATOR;
        $zf2ModulePaths .= getenv('ZF2_MODULES_TEST_PATHS') ?: (defined('ZF2_MODULES_TEST_PATHS') ? ZF2_MODULES_TEST_PATHS : '');

        static::initAutoloader();

        // use ModuleManager to load this module and it's dependencies
        $baseConfig = array(
            'module_listener_options' => array(
                'module_paths' => explode(PATH_SEPARATOR, $zf2ModulePaths),
            ),
        );

        $config = ArrayUtils::merge($baseConfig, $testConfig);

        static::$config = $config;
    }

    public static function getApplication()
    {
        $application = \Zend\Mvc\Application::init(static::$config);
        self::createDatabase($application);

        return $application;
    }

    public static function createDatabase(\Zend\Mvc\Application $application)
    {
        // build test database
        $objectManager = $application->getServiceManager()->get('doctrine.entitymanager.orm_default');
        // Add tables
        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($objectManager);
        $schemaTool->updateSchema($objectManager->getMetadataFactory()->getAllMetadata());

        // build audit database
        $auditEntityManager = $application->getServiceManager()->get('doctrine.entitymanager.orm_zf_doctrine_audit');
        // Create database
        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($auditEntityManager);
        $schemaTool->updateSchema($auditEntityManager->getMetadataFactory()->getAllMetadata());

        // Run audit fixtures
        $dataFixtureManager = $application->getServiceManager()
            ->build('ZF\Doctrine\DataFixture\DataFixtureManager', ['group' => 'zf-doctrine-audit']);

        $loader = new Loader();
        $purger = new ORMPurger();
        $executor = new ORMExecutor($auditEntityManager, $purger);

        foreach ($dataFixtureManager->getAll() as $fixture) {
            $loader->addFixture($fixture);
        }
        $executor->execute($loader->getFixtures(), true);

        // Build audit triggers
        $triggerTool = $application->getServiceManager()->get(TriggerTool::class);
        file_put_contents('audit_triggers.sql', $triggerTool->generate());
        // Static connect values - find a way to run triggers not from command line
        $localConfig = include(__DIR__ . '/autoload/tests.global.php');
        $ormDefaultConfig = $localConfig['doctrine']['connection']['orm_default']['params'];
        $command = "mysql -u root -h " . $ormDefaultConfig['host'] . " test < audit_triggers.sql";
        `$command`;
    }

    protected static function initAutoloader()
    {
        $vendorPath = static::findParentPath('vendor');

        if (is_readable($vendorPath . '/autoload.php')) {
            $loader = include $vendorPath . '/autoload.php';
        } else {
            $zf2Path = getenv('ZF2_PATH') ?: (defined('ZF2_PATH') ? ZF2_PATH : (is_dir($vendorPath . '/ZF2/library') ? $vendorPath . '/ZF2/library' : false));

            if (!$zf2Path) {
                throw new RuntimeException('Unable to load ZF2. Run `php composer.phar install` or define a ZF2_PATH environment variable.');
            }

            include $zf2Path . '/Zend/Loader/AutoloaderFactory.php';

        }

        AutoloaderFactory::factory(array(
            'Zend\Loader\StandardAutoloader' => array(
                'autoregister_zf' => true,
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/ZFTest',
                ),
            ),
        ));
    }

    protected static function findParentPath($path)
    {
        $dir = __DIR__;
        $previousDir = '.';
        while (!is_dir($dir . '/' . $path)) {
            $dir = dirname($dir);
            if ($previousDir === $dir) return false;
            $previousDir = $dir;
        }
        return $dir . '/' . $path;
    }
}

Bootstrap::init();
