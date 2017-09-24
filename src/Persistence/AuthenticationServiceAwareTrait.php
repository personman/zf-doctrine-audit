<?php

namespace ZF\Doctrine\Audit\Persistence;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * This is a convenience class for implementation in a project
 * and not used internally to this module
 */
trait AuthenticationServiceAwareTrait
{
    protected $authenticationService;

    // @codeCoverageIgnoreStart
    public function setAuthenticationService($authenticationService)
    {
        $this->authenticationService = $authenticationService;

        return $this;
    }

    public function getAuthenticationService()
    {
        return $this->authenticationService;
    }
    // @codeCoverageIgnoreEnd
}
