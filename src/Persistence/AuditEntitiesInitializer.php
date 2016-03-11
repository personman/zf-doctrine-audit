<?php

namespace ZF\Doctrine\Audit\Persistence;

use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AuditEntitiesInitializer implements InitializerInterface
{
    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        if ($instance instanceof AuditEntitiesAwareInterface) {
            if (method_exists($serviceLocator, 'getServiceLocator')) {
                $serviceLocator = $serviceLocator->getServiceLocator();
            }

            $config = $serviceLocator->get('Config')['zf-doctrine-audit'];
            $instance->setAuditEntities($config['entities']);
        }
    }
}
