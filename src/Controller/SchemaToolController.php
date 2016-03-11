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
use ZF\Doctrine\Audit\Persistence;

class SchemaToolController extends AbstractActionController implements
    Persistence\AuditObjectManagerAwareInterface
{
    use Persistence\AuditObjectManagerAwareTrait;

    public function updateAction()
    {
        $console = $this->getServiceLocator()->get('console');

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console.');
        }

        $classes = array();
        $metas = $this->getAuditObjectManager()->getMetadataFactory()->getAllMetadata();
        foreach ($metas as $meta) {
            $classes[] = $meta;
        }

        $schemaTool = new SchemaTool($this->getAuditObjectManager());
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
