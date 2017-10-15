<?php

namespace ZFTest\Doctrine\Audit\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class User
{
    protected $id;
    protected $name;
    protected $album;

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getAlbum()
    {
        return $this->album;
    }

    public function __construct()
    {
        $this->album = new ArrayCollection();
    }

    public function addAlbum(Album $album)
    {
        $this->album->add($album);
    }

    public function removeAlbum(Album $album)
    {
        $this->album->removeEntity($album);
    }
}
