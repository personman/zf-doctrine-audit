<?php

namespace ZF\Doctrine\Audit\Persistence;

use Doctrine\Common\Collections\ArrayCollection;

trait EntityConfigCollectionAwareTrait
{
    protected $entityConfigCollection = [];

    public function setEntityConfigCollection(ArrayCollection $entityConfigCollection)
    {
        $this->entityConfigCollection = $entityConfigCollection;

        return $this;
    }

    public function getEntityConfigCollection(): ArrayCollection
    {
        return $this->entityConfigCollection;
    }
}
