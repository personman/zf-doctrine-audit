<?php

namespace ZF\Doctrine\Audit\Persistence;

use Doctrine\Common\Persistence\ObjectManager;

trait AuthenticationServiceAwareTrait
{
    protected $authenticationService;

    public function setAuthenticationService($authenticationService)
    {
        $this->authenticationService = $authenticationService;

        return $this;
    }

    public function getAuthenticationService()
    {
        return $this->authenticationService;
    }
}
