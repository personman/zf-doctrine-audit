<?php

namespace ZF\Doctrine\Audit\Fixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use ZF\Doctrine\Audit\Entity;

class FieldStatusFixture implements
    FixtureInterface
{
    public function load(ObjectManager $auditObjectManager)
    {
        // Add FieldStatus
        foreach (array('active', 'inactive') as $name) {
            $fieldStatus = $auditObjectManager
                ->getRepository('ZF\Doctrine\Audit\Entity\FieldStatus')
                ->findOneBy([
                    'name' => $name,
                ]);

            if (! $fieldStatus) {
                $fieldStatus = new Entity\FieldStatus();
                $fieldStatus->setName($name);

                $auditObjectManager->persist($fieldStatus);
            }
        }

        $auditObjectManager->flush();
    }
}
