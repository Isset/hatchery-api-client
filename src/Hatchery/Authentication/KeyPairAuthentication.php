<?php

namespace Hatchery\Authentication;

use Exception;

class KeyPairAuthentication implements AuthenticationInterface
{
    /**
     * @var string
     */
    private $consumerKey;

    /**
     * @var string
     */
    private $privateKey;

    /**
     * @var string
     */
    private $tokenPath;

    public function __construct($consumerKey, $privateKey, $tokenCacheLocation = false)
    {
        $this->consumerKey = $consumerKey;
        $this->privateKey = $privateKey;

        if (!$tokenCacheLocation) {
            $tokenCacheLocation = __DIR__ . '/../Cache/';
        }
        $tokenCacheLocation = rtrim($tokenCacheLocation, '/') . '/';
        if (!is_writable($tokenCacheLocation)) {

            throw new Exception('token cache location isn\'t writable: ' . $tokenCacheLocation);
        }

        $this->tokenPath = $tokenCacheLocation . $consumerKey . '-token';
    }

    /**
     * @return string
     */
    public function getConsumerKey()
    {
        return $this->consumerKey;
    }

    /**
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * @return string
     */
    public function getTokenPath()
    {
        return $this->tokenPath;
    }

}
