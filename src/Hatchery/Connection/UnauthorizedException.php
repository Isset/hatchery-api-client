<?php
declare(strict_types=1);

namespace Hatchery\Connection;

class UnauthorizedException extends HatcheryClientException
{
    protected $response;

    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }
}
