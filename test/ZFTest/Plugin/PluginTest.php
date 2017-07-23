<?php

namespace ZFTest\Doctrine\Audit\Plugin;

use ZF\Doctrine\Audit\Plugin\AuditPlugin;
use ZFTest\Doctrine\Audit\Bootstrap;
use ZFTest\Doctrine\Audit\Entity;
use PHPUnit_Framework_TestCase;

class PluginTest extends PHPUnit_Framework_TestCase
{
    function testGetAuditPlugin()
    {
        $serviceManager = Bootstrap::getApplication()->getServiceManager();

        $objectManager = $serviceManager->get('doctrine.entitymanager.orm_default');

        $this->assertInstanceOf(
            AuditPlugin::class,
            $objectManager->getRepository(Entity\Artist::class)->plugin('audit')
        );
    }
}