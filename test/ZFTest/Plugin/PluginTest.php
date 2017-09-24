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

        $auditPlugin = $objectManager->getRepository(Entity\Artist::class)->plugin('audit');

        $this->assertInstanceOf(
            AuditPlugin::class,
            $auditPlugin
        );

        $artist = new Entity\Artist();
        $artist->setName('testGetCreatedAt');
        $objectManager->persist($artist);
        $objectManager->flush();

        $createdAt = $auditPlugin->getCreatedAt($artist);

        sleep(2);

        $artist->setName('testGetCreatedAt2');
        $objectManager->persist($artist);
        $objectManager->flush();

        sleep(2);

        $artist->setName('testGetCreatedAt3');
        $objectManager->persist($artist);
        $objectManager->flush();

        $createdAtCompare = $auditPlugin->getCreatedAt($artist);

        $this->assertEquals($createdAt, $createdAtCompare);
    }
}
