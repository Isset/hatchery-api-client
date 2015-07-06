<?php

namespace Hatchery\Builder;

use Hatchery\Builder\Exception\JobBuilderException;
use Hatchery\Builder\Url\Url;
use Hatchery\Builder\ValueObjects\Number;
use Hatchery\Builder\ValueObjects\Timestamp;

/**
 * Class Stills
 * @package Hatchery\Builder
 * @author Bart Malestein <bart@isset.nl>
 */
class Stills implements ParsableInterface
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
     * @var \Hatchery\Builder\ValueObjects\Number
     */
    protected $amount;

    /**
     * @var Timestamp[]
     */
    protected $timestamps;

    /**
     * @var string
     */
    protected $format;

    /**
     * @param Url $url
     */
    public function __construct(Url $url)
    {
        $this->url = $url;
        $this->timestamps = [];
        $this->width = null;
        $this->height = null;
        $this->amount = null;
        $this->format = null;
    }

    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->url->modifyFilename($filename);
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
     * @param \Hatchery\Builder\ValueObjects\Number $amount
     * @throws JobBuilderException
     */
    public function setAmount(Number $amount)
    {
        if (count($this->timestamps) > 0) {
            throw new JobBuilderException('Cannot use amount in stills when using timestamps');
        }

        $this->amount = $amount;
    }

    /**
     * @param Timestamp $timestamp
     * @throws JobBuilderException
     */
    public function addTimestamp(Timestamp $timestamp)
    {
        if ($this->amount !== null) {
            throw new JobBuilderException('Cannot use timestamp in stills when using amount');
        }

        $this->timestamps[] = $timestamp;
    }

    /**
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }



    /**
     * @return array
     */
    public function parse()
    {
        $data = [];
        $data['base_url'] = $this->url->parseBaseUrl();

        if ($this->width !== null) {
            $data['width'] = $this->width->getValue();
        }
        if ($this->height !== null) {
            $data['height'] = $this->height->getValue();
        }
        if ($this->amount !== null) {
            $data['amount'] = $this->amount->getValue();
        }
        if ($this->format !== null) {
            $data['format'] = $this->format;
        }

        foreach ($this->timestamps as $timestamp) {
            $data['timestamps'][] = $timestamp->getValue();
        }


        return $data;
    }
}
