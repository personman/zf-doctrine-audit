<?php

namespace ZF\Doctrine\Audit\Persistence;

use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AuthenticationServiceInitializer implements InitializerInterface
{
    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        if ($instance instanceof AuthenticationServiceAwareInterface) {
            if (method_exists($serviceLocator, 'getServiceLocator')) {
                $serviceLocator = $serviceLocator->getServiceLocator();
            }

            $config = $serviceLocator->get('Config')['zf-doctrine-audit'];

            $instance->setAuthenticationService(
                $serviceLocator->get($config['authentication_service'])
            );
        }
    }
}
