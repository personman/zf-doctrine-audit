<?php

namespace ZF\Doctrine\Audit\Persistence;

use Interop\Container\ContainerInterface;

class ObjectManagerInitializer
{
    public function __invoke(ContainerInterface $container, $instance)
    {
        if ($instance instanceof ObjectManagerAwareInterface) {
            $config = $container->get('config')['zf-doctrine-audit'];
            $instance->setObjectManager(
                $container->get($config['target_object_manager'])
            );
        }
    }
}
