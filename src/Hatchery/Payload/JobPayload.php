<?php

namespace Hatchery\Payload;

use Hatchery\Builder\Job;

/**
 * Class JobPayload
 * @package Hatchery\Payload
 * @author Bart Malestein <bart@isset.nl>
 */
class JobPayload extends Payload
{
    /**
     * @param $url
     * @param Job $job
     */
    public function __construct($url, Job $job)
    {
        parent::__construct($url);
        $this->data = $job->parse();
    }
}
