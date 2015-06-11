<?php

namespace Hatchery\Builder\Exception;

use Exception;

/**
 * Class JobBuilderException
 * @package Hatchery\Builder\Exception
 * @author Bart Malestein <bart@isset.nl>
 */
class JobBuilderException extends Exception{
    /**
     * @param string $message
     */
    public function __construct($message)
    {
        parent::__construct($message);
    }
}