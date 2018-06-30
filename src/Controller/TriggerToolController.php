<?php

namespace ZF\Doctrine\Audit\Controller;

use Zend\Mvc\Console\Controller\AbstractConsoleController;
use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\Console\ColorInterface as Color;
use Zend\Console\Prompt;
use ZF\Doctrine\Audit\Tools\TriggerTool;
use RuntimeException;

final class TriggerToolController extends AbstractConsoleController
{
    private $triggerTool;

    public function __construct(Console $console, TriggerTool $triggerTool)
    {
        $this->setConsole($console);
        $this->triggerTool = $triggerTool;
    }

    public function createAction()
    {
        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (! $this->getRequest() instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console.');
        }

        $this->getConsole()->write($this->triggerTool->generate());
    }

    public function dropAction()
    {
        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (! $this->getRequest() instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console.');
        }

        $this->getConsole()->write($this->triggerTool->drop());
    }
}
