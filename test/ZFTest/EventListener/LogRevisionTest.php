<?php

namespace ZFTest\Doctrine\Audit\EventListener;

use ZFTest\Doctrine\Audit\Bootstrap;
use PHPUnit_Framework_TestCase;
use ZFTest\Doctrine\Audit\Entity;

class LogRevisionTest extends PHPUnit_Framework_TestCase
{
    public function testGetRevisionIdentifierValue()
    {
        $serviceManager = Bootstrap::getApplication()->getServiceManager();

        $objectManager = $serviceManager->get('doctrine.entitymanager.orm_default');
        $auditObjectManager = $serviceManager->get('doctrine.entitymanager.orm_zf_doctrine_audit');

        $revisionComment = $serviceManager->get('ZF\Doctrine\Audit\Service\RevisionComment');

        $artist = new Entity\Artist();
        $artist->setName('unittest');
        $objectManager->persist($artist);
        $revisionComment->setComment('Add Artist');
        $objectManager->flush();

        $artist->setName('unit-test');
        $revisionComment->setComment('Change Artist');
        $objectManager->flush();

        $revision = $auditObjectManager->getRepository('ZF\Doctrine\Audit\Entity\Revision')
            ->findOneBy([
                'comment' => 'Change Artist',
            ]);

        foreach ($revision->getRevisionEntity() as $revisionEntity) {
            foreach ($revisionEntity->getRevisionEntityIdentifierValue() as $value) {
                $this->assertEquals(1, $value->getValue());
            }
        }
    }
}