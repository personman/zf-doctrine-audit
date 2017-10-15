<?php

namespace ZF\Doctrine\Audit\EventListener;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use ZF\Doctrine\Audit\Tools\RevisionAuditTool;

class PostFlushFactory implements
    FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $revisionAuditTool = $container->get(RevisionAuditTool::class);

        return new $requestedName($revisionAuditTool);
    }
}
