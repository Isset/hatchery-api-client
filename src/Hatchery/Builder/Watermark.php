<?php

namespace Hatchery\Builder;

use Hatchery\Builder\Url\Url;
use Hatchery\Builder\ValueObjects\Number;

/**
 * Class Watermark
 * @package Hatchery\Builder
 * @author Bart Malestein <bart@isset.nl>
 */
class Watermark implements ParsableInterface
{
    /**
     * @var Url
     */
    protected $url;

    /**
     * @var \Hatchery\Builder\ValueObjects\Number
     */
    protected $width;

    /**
     * @var \Hatchery\Builder\ValueObjects\Number
     */
    protected $height;

    /**
     * @param Url $url
     */
    public function __construct(Url $url)
    {
        $this->url = $url;
        $this->width = null;
        $this->height = null;
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
     * @return array
     */
    public function parse()
    {
        $data = [];
        $data['url'] = $this->url->parseUrl();

        if ($this->width !== null) {
            $data['width'] = $this->width->getValue();
        }
        if ($this->height !== null) {
            $data['height'] = $this->height->getValue();
        }

        return $data;
    }
}