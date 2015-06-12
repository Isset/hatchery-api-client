<?php

namespace Hatchery\Builder;

/**
 * Class Source
 * @package Hatchery\Builder
 * @author Bart Malestein <bart@isset.nl>
 */
abstract class Source implements ParsableInterface{

    protected $reference;

    public function __construct()
    {
        $this->reference = uniqid('source_');
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }



}