<?php

namespace ZF\Doctrine\Audit\Persistence;

use Doctrine\Common\Collections\ArrayCollection;

interface JoinEntityConfigCollectionAwareInterface
{
    public function setJoinEntityConfigCollection(ArrayCollection $joinEntityConfigCollection);
    public function getJoinEntityConfigCollection(): ArrayCollection;
}
