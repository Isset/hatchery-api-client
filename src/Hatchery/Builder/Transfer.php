<?php

namespace Hatchery\Builder;

use Hatchery\Builder\Url\Url;

class Transfer extends Source
{
    /**
     * @param Source $source
     * @param Url $url
     */
    public function __construct(Source $source, Url $url)
    {
        parent::__construct();
        $this->source = $source;
        $this->url = $url;
    }

    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->url->modifyFilename($filename);
    }

    /**
     * @return array
     */
    public function parse()
    {
        return [
            'type' => 'transfer-only',
            'source' => $this->source->getReference(),
            'url' => $this->url->parseUrl()
        ];
    }
}
