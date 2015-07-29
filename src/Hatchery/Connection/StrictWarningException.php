<?php

namespace Hatchery\Connection;

/**
 * Class StrictWarningException
 * @package Hatchery\Connection
 * @author Bart Malestein <bart@isset.nl>
 */
class StrictWarningException extends \Exception
{

    /**
     * @var array
     */
    private $warnings = [];

    /**
     * @param $warnings
     */
    public function setWarnings($warnings)
    {
        if (is_array($warnings)) {
            foreach ($warnings as $warning) {
                if (isset($warning['message'])) {
                    $this->warnings[] = $warning['message'];
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getWarnings()
    {
        return $this->warnings;
    }

}