<?php

namespace ZF\Doctrine\Audit\Fixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use ZF\Doctrine\Audit\Entity;

class RevisionTypeFixture implements
    FixtureInterface
{
    public function load(ObjectManager $auditObjectManager)
    {
        // Add RevisionType
        foreach (array('insert', 'update', 'delete', 'epoch') as $name) {
            $revisionType = $auditObjectManager
                ->getRepository('ZF\Doctrine\Audit\Entity\RevisionType')
                ->findOneBy(
                    [
                    'name' => $name,
                    ]
                );

            if (! $revisionType) {
                $revisionType = new Entity\RevisionType();
                $revisionType->setName($name);

                $auditObjectManager->persist($revisionType);
            }
        }

        $auditObjectManager->flush();
    }
}
