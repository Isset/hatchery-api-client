<?php

namespace Hatchery\Connection;

interface ResponseInterface
{
    public function setStatusCode($statusCode);

    public function setContent($content);

    public function getStatusCode();

    public function getContent();

    public function getJsonResponse();
    
    public function getLocation();
}
