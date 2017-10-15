<?php

namespace ZF\Doctrine\Audit\Persistence;

use Doctrine\Common\Collections\ArrayCollection;

trait JoinEntityConfigCollectionAwareTrait
{
    protected $joinEntityConfigCollection;

    public function setJoinEntityConfigCollection(ArrayCollection $joinEntityConfigCollection)
    {
        $this->joinEntityConfigCollection = $joinEntityConfigCollection;

        return $this;
    }

    public function getJoinEntityConfigCollection(): ArrayCollection
    {
        return $this->joinEntityConfigCollection;
    }
}
