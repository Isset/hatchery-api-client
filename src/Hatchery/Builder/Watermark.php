<?php

namespace Hatchery\Builder;

use Hatchery\Builder\Exception\JobBuilderException;
use Hatchery\Builder\Url\Url;

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
    private $url;

    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    /**
     * @param Url $url
     */
    function __construct(Url $url)
    {
        $this->url = $url;
        $this->width = null;
        $this->height = null;
    }

    /**
     * @param string|int $width
     * @throws JobBuilderException
     */
    public function setWidth($width)
    {
        $width = intval($width);

        if (!is_numeric($width)) {
            throw new JobBuilderException('Watermark width should be numeric');
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
            throw new JobBuilderException('Watermark height should be numeric');
        }

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
            $data['width'] = $this->width;
        }
        if ($this->height !== null) {
            $data['height'] = $this->height;
        }

        return $data;
    }
}