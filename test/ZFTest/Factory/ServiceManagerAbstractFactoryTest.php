<?php

namespace ZFTest\Doctrine\Audit\Factory;

use ZFTest\Doctrine\Audit\Bootstrap;
use ZF\Doctrine\Audit\EventListener\LogRevision;
use ZF\Doctrine\Audit\Mapping\Driver\AuditDriver;
use ZF\Doctrine\Audit\Loader\AuditAutoloader;
use PHPUnit_Framework_TestCase;

class ServiceManagerAbstractFactoryTest extends PHPUnit_Framework_TestCase
{
    function testLogRevision()
    {
        $serviceManager = Bootstrap::getApplication()->getServiceManager();
        $logRevision = $serviceManager->get(
            'ZF\Doctrine\Audit\EventListener\LogRevision'
        );

        $this->assertTrue($logRevision instanceof LogRevision);
    }

    function testAuditDriver()
    {
        $serviceManager = Bootstrap::getApplication()->getServiceManager();
        $auditDriver = $serviceManager->get(
            'ZF\Doctrine\Audit\Mapping\Driver\AuditDriver'
        );

        $this->assertTrue($auditDriver instanceof AuditDriver);
    }

    function testAuditAutoloader()
    {
        $serviceManager = Bootstrap::getApplication()->getServiceManager();
        $auditAutoLoader = $serviceManager->get(
            'ZF\Doctrine\Audit\Loader\AuditAutoLoader'
        );

        $this->assertTrue($auditAutoLoader instanceof AuditAutoLoader);
    }
}
