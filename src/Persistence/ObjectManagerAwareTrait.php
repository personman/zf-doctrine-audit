<?php

namespace ZF\Doctrine\Audit\Persistence;

use Doctrine\Common\Persistence\ObjectManager;

trait ObjectManagerAwareTrait
{
    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $objectManager;

    public function setObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @return Doctrine\ORM\EntityManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }
}
