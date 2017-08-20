<?php

namespace ZF\Doctrine\Audit\Persistence;

use Doctrine\Common\Collections\ArrayCollection;

interface EntityConfigCollectionAwareInterface
{
    public function setEntityConfigCollection(ArrayCollection $entities);
    public function getEntityConfigCollection(): ArrayCollection;
}
