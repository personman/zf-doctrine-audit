<?php

namespace ZF\Doctrine\Audit\EventListener;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class PostFlushFactory implements
    FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $identity = $container->get('authentication')->getIdentity();
        $revisionComment = $container->get('ZF\Doctrine\Audit\RevisionComment');

        return new $requestedName($revisionComment, $identity);
    }
}