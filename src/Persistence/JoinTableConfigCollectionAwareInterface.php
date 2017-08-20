<?php

namespace ZF\Doctrine\Audit\Persistence;

use Doctrine\Common\Collections\ArrayCollection;

interface JoinTableConfigCollectionAwareInterface
{
    public function setJoinTableConfigCollection(ArrayCollection $joinTableConfigCollection);
    public function getJoinTableConfigCollection(): ArrayCollection;
}
