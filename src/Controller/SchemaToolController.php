<?php

namespace ZF\Doctrine\Audit\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\Console\ColorInterface as Color;
use Zend\Console\Prompt;
use DoctrineDataFixtureModule\Loader\ServiceLocatorAwareLoader;
use RuntimeException;
use Doctrine\ORM\Tools\SchemaTool;

class SchemaToolController extends AbstractActionController
{
    public function updateAction()
    {
        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console.');
        }

        $objectManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_zf_doctrine_audit');
        $console = $this->getServiceLocator()->get('console');

        $classes = array();
        $metas = $objectManager->getMetadataFactory()->getAllMetadata();
        foreach ($metas as $meta) {
            $classes[] = $meta;
        }

        $schemaTool = new SchemaTool($objectManager);
        try {
            $result = $schemaTool->getUpdateSchemaSql($classes, false);
        } catch (\Exception $e) {
            $console->write($e->getCode() . ": " . $e->getMessage());
            $console->write("\nExiting now");

            return;
        }

        foreach ($result as $sql) {
            $console->write($sql . ";\n");
        }
    }
}
