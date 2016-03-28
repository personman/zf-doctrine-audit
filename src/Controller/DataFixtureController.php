<?php

namespace ZF\Doctrine\Audit\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\Console\ColorInterface as Color;
use Zend\Console\Prompt;
use DoctrineDataFixtureModule\Loader\ServiceLocatorAwareLoader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use ZF\Doctrine\Audit\Persistence;
use RuntimeException;
use DateTime;

class DataFixtureController extends AbstractActionController implements
    Persistence\AuditObjectManagerAwareInterface,
    Persistence\ObjectManagerAwareInterface
{
    use Persistence\AuditObjectManagerAwareTrait;
    use Persistence\ObjectManagerAwareTrait;

    public function importAction()
    {
        $console = $this->getServiceLocator()->get('console');

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console.');
        }

        // Lodgable default fixtures
        $loader = new ServiceLocatorAwareLoader($this->getServiceLocator());
        $purger = new ORMPurger();
        $executor = new ORMExecutor($this->getAuditObjectManager(), $purger);

        $loader->loadFromDirectory(__DIR__ . '/../Fixture');
        $executor->execute($loader->getFixtures(), true);


        $console->write("Audit data fixture import complete", Color::GREEN);
    }
}
