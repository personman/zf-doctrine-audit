<?php

namespace ZF\Doctrine\Audit\Entity;

interface User
{
    public function getId();
    public function getDisplayName();
    public function getEmail();
}
