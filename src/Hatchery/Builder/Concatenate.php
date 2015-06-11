<?php

namespace Hatchery\Builder;

use Hatchery\Builder\Exception\JobBuilderException;
use Hatchery\Builder\Url\Url;
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
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    /**
     * @var int
     */
    private $outputLength;

    /**
     * @var Timestamp
     */
    private $offset;


    /**
     * @param Url $url
     */
    function __construct(Url $url)
    {
        parent::__construct();
        $this->sources = [];
        $this->url = $url;
        $this->width = null;
        $this->height = null;
        $this->deinterlace = null;
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
     * @param string|int $width
     * @throws JobBuilderException
     */
    public function setWidth($width)
    {
        $width = intval($width);

        if (!is_numeric($width)) {
            throw new JobBuilderException('Concatenate width should be numeric');
        }

        $this->width = $width;
    }

    /**
     * @param string|int $height
     * @throws JobBuilderException
     */
    public function setHeight($height)
    {
        $height = intval($height);

        if (!is_numeric($height)) {
            throw new JobBuilderException('Concatenate height should be numeric');
        }

        $this->height = $height;
    }

    /**
     * @param string|int $outputLength
     * @throws JobBuilderException
     */
    public function setOutputLength($outputLength)
    {
        $outputLength = intval($outputLength);

        if (!is_numeric($outputLength)) {
            throw new JobBuilderException('Concatenate outputLength should be numeric');
        }

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
            $data['width'] = $this->width;
        }
        if ($this->height !== null) {
            $data['height'] = $this->height;
        }
        if ($this->outputLength !== null) {
            $data['output_length'] = $this->outputLength;
        }
        if($this->offset !== null) {
            $data['seek_offset'] = $this->offset->parse();
        }
        if($this->offset !== null) {
            $data['seek_offset'] = $this->offset->parse();
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