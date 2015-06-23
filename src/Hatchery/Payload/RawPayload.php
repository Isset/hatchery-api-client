<?php

namespace Hatchery\Payload;

use Hatchery\Builder\Job;

/**
 * Class JobPayload
 * @package Hatchery\Payload
 * @author Bart Malestein <bart@isset.nl>
 */
class RawPayload extends Payload
{

    /**
     * @param $url
     * @param array $data
     */
    public function __construct($url, array $data)
    {
        parent::__construct($url);
        $this->data = $data;
    }
}