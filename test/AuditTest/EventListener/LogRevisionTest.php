<?php

namespace ZFTest\Doctrine\Audit\Service;

use ZFTest\Doctrine\Audit\Bootstrap
    , ZFTest\Doctrine\Audit\Models\LogRevision\Album
    , ZFTest\Doctrine\Audit\Models\LogRevision\Song
    , ZFTest\Doctrine\Audit\Models\LogRevision\Performer
    , Doctrine\Common\Persistence\Mapping\ClassMetadata
    , Doctrine\ORM\Tools\Setup
    , Doctrine\ORM\EntityManager
    , Doctrine\ORM\Mapping\Driver\StaticPHPDriver
    , Doctrine\ORM\Mapping\Driver\XmlDriver
    , Doctrine\ORM\Mapping\Driver\DriverChain
    , ZF\Doctrine\Audit\Mapping\Driver\AuditDriver
    , ZF\Doctrine\Audit\EventListener\LogRevision
    , Doctrine\ORM\Tools\SchemaTool
    ;

class LogRevisionTest extends \PHPUnit_Framework_TestCase
{
    private $_em;
    private $_oldEntityManager;

    public function setUp()
    {
        $this->_oldEntityManager = \ZF\Doctrine\Audit\Module::getModuleOptions()->getEntityManager();
        $this->_oldAuditedClassNames = \ZF\Doctrine\Audit\Module::getModuleOptions()->getAuditedClassNames();
        $this->_oldJoinClasses = \ZF\Doctrine\Audit\Module::getModuleOptions()->resetJoinClasses();

        $isDevMode = false;

        $config = Setup::createConfiguration($isDevMode, null, null);

        $chain = new DriverChain();

        // Use ZFC User for authentication tests
        $chain->addDriver(new XmlDriver(__DIR__ . '/../../../vendor/zf-commons/zfc-user-doctrine-orm/config/xml/zfcuser')
            , 'ZfcUser\Entity');
        $chain->addDriver(new XmlDriver(__DIR__ . '/../../../vendor/zf-commons/zfc-user-doctrine-orm/config/xml/zfcuserdoctrineorm')
            , 'ZfcUserDoctrineORM\Entity');
        $chain->addDriver(new StaticPHPDriver(__DIR__ . "/../Models"), 'ZFTest\Doctrine\Audit\Models\LogRevision');
        $chain->addDriver(new AuditDriver('.'), 'ZF\Doctrine\Audit\Entity');

        $config->setMetadataDriverImpl($chain);

        // Replace entity manager
        $moduleOptions = \ZF\Doctrine\Audit\Module::getModuleOptions();

        $conn = array(
            'driver' => 'pdo_sqlite',
            'memory' => true,
        );

        $moduleOptions->setAuditedClassNames(array(
            'ZFTest\Doctrine\Audit\Models\LogRevision\Album' => array(),
            'ZFTest\Doctrine\Audit\Models\LogRevision\Performer' => array(),
            'ZFTest\Doctrine\Audit\Models\LogRevision\Song' => array(),
            'ZFTest\Doctrine\Audit\Models\LogRevision\SingleCoverArt' => array(),
        ));

        $entityManager = EntityManager::create($conn, $config);
        $moduleOptions->setEntityManager($entityManager);
        $schemaTool = new SchemaTool($entityManager);

        // Add auditing listener
        $entityManager->getEventManager()->addEventSubscriber(new LogRevision());

        $sql = $schemaTool->getUpdateSchemaSql($entityManager->getMetadataFactory()->getAllMetadata());
        #print_r($sql);die();

        $schemaTool->createSchema($entityManager->getMetadataFactory()->getAllMetadata());

        $this->_em = $entityManager;

    }

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

    public function testOneToManyAudit()
    {
        $album = new Album;
        $album->setTitle('Test One To Many Audit');

        $song = new Song;
        $song->setTitle('Test one to many audit song > album');

        $song->setAlbum($album);
        $album->getSongs()->add($song);

        $this->_em->persist($album);
        $this->_em->persist($song);

        $this->_em->flush();


        $persistedSong = $this->_em->getRepository('ZFTest\Doctrine\Audit\Models\LogRevision\Song')->find($song->getId());

        $this->assertEquals($song, $persistedSong);
        $this->assertEquals($album, $persistedSong->getAlbum());
    }

    public function testManyToManyAudit()
    {
        $album = new Album;
        $album->setTitle('Test Many To Many Audit');

        $performer = new Performer;
        $performer->setName('Test many to many audit');

        $this->_em->persist($album);
        $this->_em->persist($performer);

        $this->_em->flush();

        $performer->getAlbums()->add($album);
        $album->getPerformers()->add($performer);

        $this->_em->flush();

        $moduleOptions = \ZF\Doctrine\Audit\Module::getModuleOptions();
        $this->assertGreaterThan(0, sizeof($moduleOptions->getJoinClasses()));

        $manyToManys = $this->_em->getRepository('ZF\Doctrine\Audit\Entity\performer_album')->findAll();
        $manyToMany = reset($manyToManys);

        $this->assertInstanceOf('ZF\Doctrine\Audit\Entity\performer_album', $manyToMany);
#        $manyToManyValues = $manyToMany->getArrayCopy();

        $this->assertEquals($album->getId(), $manyToMany->getSourceRevisionEntity()->getTargetEntity()->getId());
        $this->assertEquals($performer->getId(), $manyToMany->getTargetRevisionEntity()->getTargetEntity()->getId());
    }

    public function testAuditDeleteEntity()
    {
        $album = new Album;
        $album->setTitle('test audit delete entity');
        $this->_em->persist($album);

        $this->_em->flush();

        $this->_em->remove($album);
        $this->_em->flush();
    }

    public function testCollectionDeletion()
    {
        $album = new Album;
        $album->setTitle('Test collection deletion');

        $performer = new Performer;
        $performer->setName('Test collection deletion');

        $performer->getAlbums()->add($album);
        $album->getPerformers()->add($performer);

        $this->_em->flush();

        $performer->getAlbums()->removeElement($album);
        $album->getPerformers()->removeElement($performer);

        $this->_em->flush();

        $manyToManys = $this->_em->getRepository('ZF\Doctrine\Audit\Entity\performer_album')->findAll();

        $this->assertEquals(array(), $manyToManys);

    }

    public function tearDown()
    {
        // Replace entity manager
        $moduleOptions = \ZF\Doctrine\Audit\Module::getModuleOptions();
        $moduleOptions->setEntityManager($this->_oldEntityManager);
        \ZF\Doctrine\Audit\Module::getModuleOptions()->setAuditedClassNames($this->_oldAuditedClassNames);
        \ZF\Doctrine\Audit\Module::getModuleOptions()->resetJoinClasses($this->_oldJoinClasses);
    }
}