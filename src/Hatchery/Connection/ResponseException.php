<?php

namespace Hatchery\Connection;

class ResponseException extends HatcheryClientException
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
