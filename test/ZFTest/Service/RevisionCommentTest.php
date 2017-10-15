<?php

namespace ZFTest\Doctrine\Audit\Service;

use ZFTest\Doctrine\Audit\Bootstrap;
use PHPUnit_Framework_TestCase;
use ZF\Doctrine\Audit\RevisionComment;

class RevisionCommentTest extends PHPUnit_Framework_TestCase
{
    function testSetComment()
    {
        $serviceManager = Bootstrap::getApplication()->getServiceManager();

        $revisionComment = $serviceManager->get(RevisionComment::class);
        $revisionComment->setComment('unittest');

        $this->assertEquals('unittest', $revisionComment->getComment());
    }

    function testClearComment()
    {
        $serviceManager = Bootstrap::getApplication()->getServiceManager();

        $revisionComment = $serviceManager->get(RevisionComment::class);

        $revisionComment->setComment('unittest');
        $this->assertEquals('unittest', $revisionComment->getComment());

        $revisionComment->setComment(null);
        $this->assertEquals(null, $revisionComment->getComment());
    }
}
