<?php

namespace Hatchery\Builder;

use Hatchery\Builder\Exception\JobBuilderException;
use Hatchery\Builder\Url\Url;
use Hatchery\Builder\ValueObjects\Timestamp;

/**
 * Class Output
 * @package Hatchery\Builder
 * @author Bart Malestein <bart@isset.nl>
 */
class Output extends Source
{

    /**
     * @var Source
     */
    protected $source;

    /**
     * @var Url
     */
    protected $url;

    /**
     * @var Watermark[]
     */
    protected $watermarks;

    /**
     * @var Stills[]
     */
    protected $stills;

    /**
     * @var Caption
     */
    protected $caption;

    /**
     * @param Source $source
     * @param Url $url
     */
    function __construct(Source $source, Url $url)
    {
        parent::__construct();
        $this->source = $source;
        $this->url = $url;

        $this->width = null;
        $this->height = null;
        $this->deinterlace = null;
        $this->preset = null;
        $this->outputLength = null;
        $this->offset = null;

        $this->type = null;

        $this->caption = null;
        $this->stills = [];
        $this->watermarks = [];
    }


    public function setDeinterlaced()
    {
        $this->deinterlace = true;
    }


    /**
     *
     */
    public function setSegmented()
    {
        $this->type = 'segmented';
    }

    /**
     * @return boolean
     */
    public function isSegmented()
    {
        return $this->type === "segmented" ? true : false;
    }

    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->url->modifyFilename($filename);
    }


    /**
     * @param string|int $width
     * @throws JobBuilderException
     */
    public function setWidth($width)
    {
        $width = intval($width);

        if (!is_numeric($width)) {
            throw new JobBuilderException('Output width should be numeric');
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
            throw new JobBuilderException('Output height should be numeric');
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
            throw new JobBuilderException('Output outputLength should be numeric');
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


    /**
     * @param Timestamp $offset
     */
    public function setOffset(Timestamp $offset)
    {
        $this->offset = $offset;
    }

    /**
     * @param Watermark $watermark
     */
    public function addWatermark(Watermark $watermark)
    {
        $this->watermarks[] = $watermark;
    }

    /**
     * @param Stills $stills
     */
    public function addStills(Stills $stills)
    {
        $this->stills[] = $stills;
    }

    /**
     * @param Caption $caption
     */
    public function setCaption(Caption $caption){

        $this->caption = $caption;
    }

    /**
     * @return array
     */
    public function parse()
    {
        $data = [];
        $data['source'] = $this->source->getReference();
        $data['reference'] = $this->reference;
        $data['url'] = $this->url->parseUrl();

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
        if ($this->deinterlace === true) {
            $data['deinterlace'] = true;
        }
        if($this->preset !== null) {
            $data['preset'] = $this->preset;
        }
        if ($this->type !== null) {
            $data['type'] = $this->type;
        }
        if ($this->caption !== null) {
            $data['caption_url'] = $this->caption->parse();
        }
        foreach ($this->stills as $still) {
            $data['stills'][] = $still->parse();
        }
        foreach ($this->watermarks as $watermark) {
            $data['watermarks'][] = $watermark->parse();
        }

        return $data;

    }
}