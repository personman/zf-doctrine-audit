<?php

namespace ZFTest\Doctrine\Audit\Loader;

use ZFTest\Doctrine\Audit\Bootstrap
    , ZFTest\Doctrine\Audit\Models\Autoloader\Album
    , Doctrine\Common\Persistence\Mapping\ClassMetadata
    , Doctrine\ORM\Tools\Setup
    , Doctrine\ORM\EntityManager
    , Doctrine\ORM\Mapping\Driver\StaticPHPDriver
    , Doctrine\ORM\Mapping\Driver\XmlDriver
    , Doctrine\ORM\Mapping\Driver\DriverChain
    , ZF\Doctrine\Audit\Mapping\Driver\AuditDriver
    , Doctrine\ORM\Tools\SchemaTool
    ;

class AuditAutoloaderTest extends \PHPUnit_Framework_TestCase
{
    private $_em;
    private $_oldEntityManager;
    private $_oldAuditedClassNames;

    public function setUp()
    {
        $this->_oldEntityManager = \ZF\Doctrine\Audit\Module::getModuleOptions()->getEntityManager();
        $this->_oldAuditedClassNames = \ZF\Doctrine\Audit\Module::getModuleOptions()->getAuditedClassNames();


        $isDevMode = true;

        $config = Setup::createConfiguration($isDevMode, null, null);

        $chain = new DriverChain();
        // zfc user is required
        $chain->addDriver(new XmlDriver(__DIR__ . '/../../../vendor/zf-commons/zfc-user-doctrine-orm/config/xml/zfcuser')
            , 'ZfcUser\Entity');
        $chain->addDriver(new XmlDriver(__DIR__ . '/../../../vendor/zf-commons/zfc-user-doctrine-orm/config/xml/zfcuserdoctrineorm')
            , 'ZfcUserDoctrineORM\Entity');
        $chain->addDriver(new StaticPHPDriver(__DIR__ . "/../Models"), 'ZFTest\Doctrine\Audit\Models\Autoloader');
        $chain->addDriver(new AuditDriver('.'), 'ZF\Doctrine\Audit\Entity');

        // Replace entity manager
        $moduleOptions = \ZF\Doctrine\Audit\Module::getModuleOptions();
        $moduleOptions->setAuditedClassNames(array(
            'ZFTest\Doctrine\Audit\Models\Autoloader\Album' => array(),
            'ZFTest\Doctrine\Audit\Models\Autoloader\Performer' => array(),
            'ZFTest\Doctrine\Audit\Models\Autoloader\Song' => array(),
        ));


        $config->setMetadataDriverImpl($chain);

        $conn = array(
            'driver' => 'pdo_sqlite',
            'memory' => true,
        );

        $entityManager = EntityManager::create($conn, $config);
        $moduleOptions->setEntityManager($entityManager);
        $schemaTool = new SchemaTool($entityManager);

        $schemaTool->createSchema($entityManager->getMetadataFactory()->getAllMetadata());

        $this->_em = $entityManager;

    }

    public function testTrue()
    {
        $this->assertTrue(true);
    }

/*
    // If we reach this function then the audit driver has worked
    public function testAuditCreateUpdateDelete()
    {
        $album = new Album;
        $album->setTitle('Test entity lifecycle: CREATE');

        $this->_em->persist($album);
        $this->_em->flush();

        $album->setTitle('Test entity lifecycle: UPDATE');

        $this->_em->flush();

        $album->setTitle('Test entity lifecycle: DELETE');

        $this->_em->flush();


        $this->assertTrue(true);
    }
*/
    public function tearDown()
    {
        // Replace entity manager
        $moduleOptions = \ZF\Doctrine\Audit\Module::getModuleOptions();
        $moduleOptions->setEntityManager($this->_oldEntityManager);
        \ZF\Doctrine\Audit\Module::getModuleOptions()->setAuditedClassNames($this->_oldAuditedClassNames);
    }
}