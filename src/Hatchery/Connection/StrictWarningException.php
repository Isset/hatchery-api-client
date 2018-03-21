<?php

namespace Hatchery\Connection;

class StrictWarningException extends HatcheryClientException
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
