<?php

namespace ZF\Doctrine\Audit\Controller;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

final class TriggerToolControllerFactory implements
    FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $console = $container->get('console');
        $triggerTool = $container->get('ZF\Doctrine\Audit\Tools\TriggerTool');

        return new $requestedName($console, $triggerTool);
    }
}