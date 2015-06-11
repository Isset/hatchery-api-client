<?php

namespace Hatchery\Builder\Exception;

use Exception;

/**
 * Class InvalidUrlException
 *
 * @package IssetBV\Hatchery\UrlBundle\Exception
 * @author Tim Fennis <tim@isset.nl>
 */
class InvalidUrlException extends Exception
{
    /**
     * @param string $message
     */
    public function __construct($message)
    {
        parent::__construct($message);
    }
}