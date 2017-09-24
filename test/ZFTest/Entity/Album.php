<?php

namespace ZFTest\Doctrine\Audit\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class Album
{
    protected $id;
    protected $name;
    protected $artist;
    protected $user;

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

    public function getArtist()
    {
        return $this->artist;
    }

    public function setArtist(Artist $artist)
    {
        $this->artist = $artist;

        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function __construct()
    {
        $this->user = new ArrayCollection();
    }

    public function addUser(User $album)
    {
        $this->user->add($user);
    }

    public function removeUser(User $user)
    {
        $this->user->removeEntity($user);
    }
}
