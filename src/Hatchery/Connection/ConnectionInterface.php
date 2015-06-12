<?php

namespace Hatchery\Connection;

use Hatchery\Payload\Payload;

interface ConnectionInterface
{

    public function sendPayload(Payload $payload);

}