<?php

namespace ZF\Doctrine\Audit\Tools;

use Exception;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use ZF\Doctrine\Audit\AuditOptions;
use ZF\Doctrine\Audit\RevisionComment;

class RevisionAuditToolFactory implements
    FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config')['zf-doctrine-audit'];

        $objectManager = $container->get($config['target_object_manager']);
        $revisionComment = $container->get(RevisionComment::class);

        $authentication = null;
        try {
            $authentication = $container->get('authentication');
        } catch (Exception $e) {
        }

        return new $requestedName($objectManager, $revisionComment, $authentication);
    }
}
