<?php

namespace ZFTest\Doctrine\Audit\Entity;

class Artist
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
}
