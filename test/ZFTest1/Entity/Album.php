<?php

namespace ZFTest\Doctrine\Audit\Entity;

class Album
{
    protected $id;
    protected $name;
    protected $artist;

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
}
