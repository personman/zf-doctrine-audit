<?php

namespace ZF\Doctrine\Audit\Factory;

class ServiceManagerAbstractFactory extends AbstractAbstractFactory 
{
    protected $factoryClasses = [
        'ZF\Doctrine\Audit\EventListener\LogRevision' =>
            'ZF\Doctrine\Audit\EventListener\LogRevision',
        'ZF\Doctrine\Audit\Mapping\Driver\AuditDriver' =>
            'ZF\Doctrine\Audit\Mapping\Driver\AuditDriver',
        'ZF\Doctrine\Audit\Service\AuditService' =>
            'ZF\Doctrine\Audit\Service\AuditService',
        'ZF\Doctrine\Audit\Loader\AuditAutoloader' =>
            'ZF\Doctrine\Audit\Loader\AuditAutoloader',
    ];
}
