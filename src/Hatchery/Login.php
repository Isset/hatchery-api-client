<?php

namespace Hatchery;

use Hatchery\Payload\Payload;

/**
 * Class Login
 * @package Hatchery
 * @author Bart Malestein <bart@isset.nl>
 */
class Login extends Payload
{
    private $consumerKey;
    private $privateKey;

    public function __construct($url, $consumerKey, $privateKey)
    {
        $this->consumerKey = $consumerKey;
        $this->privateKey = $privateKey;
        parent::__construct($url);
    }

    public function getPostData()
    {
        $time = time();
        $this->setPostData('consumer_key', $this->consumerKey);
        $this->setPostData('time', $time);
        $this->setPostData('hash', crypt($time . '' . $this->privateKey . '' . $this->consumerKey, '$6$rounds=9001$' . $this->consumerKey . '$'));
        return parent::getPostData();
    }
}
