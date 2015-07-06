<?php

namespace Hatchery\Builder;

use Hatchery\Builder\Exception\JobBuilderException;
use Hatchery\Builder\Url\Url;

/**
 * Class Playlist
 * @package Hatchery\Builder
 * @author Bart Malestein <bart@isset.nl>
 */
class Playlist extends Source
{
    /**
     * @var Url
     */
    protected $url;

    /**
     * @var Output[]
     */
    protected $streams;

    /**
     * @param Url $url
     */
    public function __construct(Url $url)
    {
        parent::__construct();
        $this->url = $url;
        $this->streams = [];
    }

    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->url->modifyFilename($filename);
    }

    /**
     * @param Output $output
     * @throws JobBuilderException
     */
    public function addSegmentedOutput(Output $output)
    {
        if (false === $output->isSegmented()) {
            throw new JobBuilderException('Segmented output expected in playlist');
        }

        $this->streams[] = $output;
    }

    /**
     * @return array
     */
    public function parse()
    {
        $data = [];

        $data['reference'] = $this->reference;
        $data['type'] = 'playlist';
        $data['url'] = $this->url->parseUrl();

        $data['streams'] = [];
        foreach ($this->streams as $stream) {
            $data['streams'][] = $stream->getReference();
        }

        return $data;
    }
}
