<?php

namespace ZF\Doctrine\Audit\Persistence;

use Doctrine\Common\Collections\ArrayCollection;

trait JoinTableConfigCollectionAwareTrait
{
    protected $joinTableConfigCollection;

    public function setJoinTableConfigCollection(ArrayCollection $joinTableConfigCollection)
    {
        $this->joinTableConfigCollection = $joinTableConfigCollection;

        return $this;
    }

    public function getJoinTableConfigCollection(): ArrayCollection
    {
        return $this->joinTableConfigCollection;
    }
}
