<?php

namespace ZF\Doctrine\Audit\EventListener;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use ZF\Doctrine\Audit\RevisionComment;

class PostFlushFactory implements
    FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $authentication = $container->get('authentication');
        $revisionComment = $container->get(RevisionComment::class);

        return new $requestedName($revisionComment, $authentication);
    }
}
