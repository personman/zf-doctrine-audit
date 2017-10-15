<?php

namespace ZF\Doctrine\Audit\Controller;

use RuntimeException;
use Zend\Mvc\Console\Controller\AbstractConsoleController;
use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Adapter\AdapterInterface as Console;
use ZF\Doctrine\Audit\Tools\EpochTool;

final class EpochToolController extends AbstractConsoleController
{
    private $epochTool;

    public function __construct(Console $console, EpochTool $epochTool)
    {
        $this->setConsole($console);
        $this->epochTool = $epochTool;
    }

    public function importAction()
    {
        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (! $this->getRequest() instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console.');
        }

        $this->getConsole()->write($this->epochTool->generate());
    }
}
