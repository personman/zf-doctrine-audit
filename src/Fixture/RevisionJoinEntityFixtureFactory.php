<?php

namespace ZF\Doctrine\Audit\Fixture;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Doctrine\Common\Collections\ArrayCollection;

class RevisionJoinEntityFixtureFactory implements
    FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->getServiceLocator()->get('config')['zf-doctrine-audit'];

        $instance = new $requestedName();

        $instance->setJoinEntityConfigCollection(new ArrayCollection($config['joinEntities']));
        $instance->setObjectManager($container->getServiceLocator()->get($config['target_object_manager']));

        return $instance;
    }
}
