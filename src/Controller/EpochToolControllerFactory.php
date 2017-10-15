<?php

namespace ZF\Doctrine\Audit\Controller;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use ZF\Doctrine\Audit\Tools\EpochTool;

final class EpochToolControllerFactory implements
    FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config')['zf-doctrine-audit'];

        $console = $container->get('console');
        $epochTool = $container->get(EpochTool::class);

        return new $requestedName($console, $epochTool);
    }
}
