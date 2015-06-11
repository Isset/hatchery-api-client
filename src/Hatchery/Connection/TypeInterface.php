<?php

namespace Hatchery\Connection;

use Hatchery\Payload\Payload;

interface TypeInterface
{

    public function sendPayload(Payload $payload);

}