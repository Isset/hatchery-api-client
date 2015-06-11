<?php

namespace Hatchery\Builder;

use Hatchery\Builder\Url\Url;


/**
 * Class Input
 * @package Hatchery\Builder
 * @author Bart Malestein <bart@isset.nl>
 */
class Input extends Source
{

    /**
     * @var Url
     */
    private $url;

    /**
     * @param Url $url
     */
    function __construct(Url $url)
    {
        parent::__construct();

        $this->url = $url;
    }

    public function parse()
    {
        return [
            'url' => $this->url->parseUrl(),
            'reference' => $this->reference
        ];
    }
}