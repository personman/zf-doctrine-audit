<?php

namespace ZF\Doctrine\Audit\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\Console\ColorInterface as Color;
use Zend\Console\Prompt;
use ZF\Doctrine\Audit\Tools\TriggerTool;
use RuntimeException;

final class TriggerToolController extends AbstractActionController
{
    private $console;
    private $triggerTool;

    public function __construct(Console $console, TriggerTool $triggerTool)
    {
        $this->console = $console;
        $this->triggerTool = $triggerTool;
    }

    public function createAction()
    {
        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        $request = $this->getRequest();
        if (! $request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console.');
        }

        $this->console->write($this->triggerTool->generate());
    }
}
