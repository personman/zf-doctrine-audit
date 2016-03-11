<?php

namespace ZF\Doctrine\Audit\Persistence;

interface AuthenticationServiceAwareInterface
{
    public function setAuthenticationService($authenticationService);
    public function getAuthenticationService();
}
