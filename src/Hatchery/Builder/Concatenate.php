<?php

namespace Hatchery\Builder;

use Hatchery\Builder\Url\Url;
use Hatchery\Builder\ValueObjects\Number;
use Hatchery\Builder\ValueObjects\Timestamp;

/**
 * Class Concatenate
 * @package Hatchery\Builder
 * @author Bart Malestein <bart@isset.nl>
 */
class Concatenate extends Source
{
    /**
     * @var Source[]
     */
    protected $sources;

    /**
     * @var string
     */
    protected $preset;

    /**
     * @var boolean
     */
    protected $deinterlace;

    /**
     * @var \Hatchery\Builder\ValueObjects\Number
     */
    protected $width;

    /**
     * @var \Hatchery\Builder\ValueObjects\Number
     */
    protected $height;

    /**
     * @var \Hatchery\Builder\ValueObjects\Number
     */
    protected $outputLength;

    /**
     * @var Timestamp
     */
    protected $offset;


    /**
     * @param Url $url
     */
    public function __construct(Url $url)
    {
        parent::__construct();
        $this->sources = [];
        $this->url = $url;
        $this->width = null;
        $this->height = null;
        $this->deinterlace = false;
        $this->preset = null;
        $this->outputLength = null;
        $this->offset = null;
    }

    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->url->modifyFilename($filename);
    }

    /**
     * @param Source $source
     */
    public function addSource(Source $source)
    {
        $this->sources[] = $source;
    }

    /**
     * @param \Hatchery\Builder\ValueObjects\Number $width
     */
    public function setWidth(Number $width)
    {
        $this->width = $width;
    }

    /**
     * @param \Hatchery\Builder\ValueObjects\Number $height
     */
    public function setHeight(Number $height)
    {
        $this->height = $height;
    }

    /**
     * @param \Hatchery\Builder\ValueObjects\Number $outputLength
     */
    public function setOutputLength(Number $outputLength)
    {
        $this->outputLength = $outputLength;
    }

    /**
     * @param string $preset
     */
    public function setPreset($preset)
    {
        $this->preset = $preset;
    }

    public function setDeinterlaced()
    {
        $this->deinterlace = true;
    }

    /**
     * @param Timestamp $offset
     */
    public function setOffset(Timestamp $offset)
    {
        $this->offset = $offset;
    }


    /**
     * @return array
     */
    public function parse()
    {
        $data = [];
        $data['type'] = 'concatenate';
        $data['url'] = $this->url->parseUrl();

        $data['source'] = [];
        foreach ($this->sources as $source) {
            $data['source'][] = $source->getReference();
        }

        if ($this->width !== null) {
            $data['width'] = $this->width->getValue();
        }
        if ($this->height !== null) {
            $data['height'] = $this->height->getValue();
        }
        if ($this->outputLength !== null) {
            $data['output_length'] = $this->outputLength->getValue();
        }
        if($this->offset !== null) {
            $data['seek_offset'] = $this->offset->getValue();
        }
        if($this->offset !== null) {
            $data['seek_offset'] = $this->offset->getValue();
        }
        if ($this->deinterlace === true) {
            $data['deinterlace'] = true;
        }
        if($this->preset !== null) {
            $data['preset'] = $this->preset;
        }

        $data['reference'] = $this->reference;

        return $data;
    }


}