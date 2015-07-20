<?php

namespace Hatchery\Authentication;

class TokenAuthentication implements AuthenticationInterface
{

    /**
     * @var string
     */
    private $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

}