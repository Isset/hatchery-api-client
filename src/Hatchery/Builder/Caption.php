<?php

namespace Hatchery\Builder;

use Hatchery\Builder\Url\Url;

/**
 * Class Caption
 * @package Hatchery\Builder
 * @author Bart Malestein <bart@isset.nl>
 */
class Caption implements ParsableInterface
{

    /**
     * @var Url
     */
    private $url;

    function __construct(Url $url)
    {

        $this->url = $url;
    }

    public function parse()
    {
        return $this->url->parseUrl();
    }
}