<?php

namespace ZF\Doctrine\Audit\Plugin;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use ZF\Doctrine\Audit\RevisionComment;
use ZF\Doctrine\Audit\Tools\RevisionAuditTool;

class AuditPluginFactory implements
    FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config')['zf-doctrine-audit'];

        $instance = new $requestedName($options);
        $instance->setAuditObjectManager($container->get($config['audit_object_manager']));
        $instance->setRevisionComment($container->get(RevisionComment::class));
        $instance->setRevisionAuditTool($container->get(RevisionAuditTool::class));

        return $instance;
    }
}
