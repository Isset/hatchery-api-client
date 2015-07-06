<?php

namespace Hatchery\Connection\Curl;

use Hatchery\Connection\ResponseInterface;

class CurlResponse implements ResponseInterface
{
    private $content;
    private $headers;
    private $statusCode;

    public function __construct($statusCode, $headers, $content)
    {
        $this->statusCode = $statusCode;
        $this->headers    = $headers;
        $this->content    = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function setContent($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    public function setStatusCode($content)
    {
        $this->content = $content;
    }

    public function getJsonResponse()
    {
        return json_decode($this->content, true);
    }

    public function getLocation()
    {
        return $this->getHeaderByName('Location');
    }

    public function getHeaderByName($name)
    {
        $headers = explode("\n", $this->headers);
        
        foreach ($headers as $header) {
            if (empty($header)) {
                continue;
            }
            $keyVal = explode(": ", $header);
            if ($keyVal[0] === $name) {
                return trim($keyVal[1], " \t\n\r\0\x0B");
            }
        }
        
        return false;
    }
}
