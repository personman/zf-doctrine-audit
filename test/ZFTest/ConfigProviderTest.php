<?php

namespace ZFTest\Doctrine\Audit;

use ZFTest\Doctrine\Audit\Bootstrap;
use PHPUnit_Framework_TestCase;
use ZF\Doctrine\Audit\ConfigProvider;

class ConfigProviderTest extends PHPUnit_Framework_TestCase
{
    function testInvoke()
    {
        $configProvider = new ConfigProvider();

        $this->assertTrue(is_array($configProvider()));
    }
}
