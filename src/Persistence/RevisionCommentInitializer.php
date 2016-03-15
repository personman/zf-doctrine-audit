<?php

namespace ZF\Doctrine\Audit\Persistence;

use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RevisionCommentInitializer implements InitializerInterface
{
    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        if ($instance instanceof RevisionCommentAwareInterface) {
            if (method_exists($serviceLocator, 'getServiceLocator')) {
                $serviceLocator = $serviceLocator->getServiceLocator();
            }

            $instance->setAuditService(
                $serviceLocator->get('ZF\Doctrine\Audit\Service\RevisionComment')
            );
        }
    }
}
