<?php

namespace Hatchery\Builder;

use Hatchery\Builder\Exception\JobBuilderException;
use Hatchery\Builder\Url\Url;
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
     * @var int
     */
    private $amount;

    /**
     * @var Timestamp[]
     */
    private $timestamps;

    /**
     * @var string
     */
    private $format;

    /**
     * @param Url $url
     */
    function __construct(Url $url)
    {
        $this->url = $url;
        $this->timestamps = [];
        $this->width = null;
        $this->height = null;
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
     * @param string|int $width
     * @throws JobBuilderException
     */
    public function setWidth($width)
    {
        $width = intval($width);

        if (!is_numeric($width)) {
            throw new JobBuilderException('Stills width should be numeric');
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
            throw new JobBuilderException('Stills height should be numeric');
        }

        $this->height = $height;
    }

    /**
     * @param string|int $amount
     * @throws JobBuilderException
     */
    public function setAmount($amount)
    {
        if (count($this->timestamps) > 0) {
            throw new JobBuilderException('Cannot use amount in stills when using timestamps');
        }

        $amount = intval($amount);

        if (!is_numeric($amount)) {
            throw new JobBuilderException('Stills amount should be numeric');
        }

        $this->amount = $amount;
    }

    /**
     * @param Timestamp $timestamp
     * @throws JobBuilderException
     */
    public function addTimestamp(Timestamp $timestamp){
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
            $data['width'] = $this->width;
        }
        if ($this->height !== null) {
            $data['height'] = $this->height;
        }
        if ($this->amount !== null) {
            $data['amount'] = $this->amount;
        }
        if ($this->format !== null) {
            $data['format'] = $this->format;
        }

        foreach ($this->timestamps as $timestamp) {

            $data['timestamps'][] = $timestamp->parse();
        }


        return $data;

    }
}