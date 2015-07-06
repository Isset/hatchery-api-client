<?php

namespace Hatchery\Payload;

class Payload
{
    protected $data = array();
    protected $headers = array();
    protected $url;
    protected $method = 'post';

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setHeader($header, $value)
    {
        $this->headers[$header] = $value;
        return $this;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function setPostData($key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }

    public function getPostData()
    {
        return $this->data;
    }

    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    public function getMethod()
    {
        return $this->method;
    }
}
