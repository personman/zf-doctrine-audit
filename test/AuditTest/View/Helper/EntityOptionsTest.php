<?php

namespace ZFTest\Doctrine\Audit\View\Helper;

use ZFTest\Doctrine\Audit\Bootstrap
    , ZFTest\Doctrine\Audit\Models\Bootstrap\Album
    ;

class EntityOptionsTest extends \PHPUnit_Framework_TestCase
{
    public function testRevisionsAreReturnedInPaginator()
    {
        $sm = Bootstrap::getApplication()->getServiceManager();
        $helper = $sm->get('viewhelpermanager')->get('auditEntityOptions');

        $helper('ZFTest\Doctrine\Audit\Models\Bootstrap\Song');
        $helper();
    }
}
