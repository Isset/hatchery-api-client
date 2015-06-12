<?php

namespace Hatchery\Builder;

use Hatchery\Builder\Url\Url;
use Hatchery\Builder\ValueObjects\Number;
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
     * @var string
     */
    protected $type;


    /**
     * @param Source $source
     * @param Url $url
     */
    public function __construct(Source $source, Url $url)
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

    /**
     *
     */
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
     * @return array
     */
    public function parse()
    {
        $data = [];
        $data['source'] = $this->source->getReference();
        $data['reference'] = $this->reference;
        $data['url'] = $this->url->parseUrl();

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