<?php

namespace Hatchery\Builder\ValueObjects;
use Hatchery\Builder\Exception\JobBuilderException;

/**
 * Class Number
 * @package Hatchery\Builder\ValueObjects
 * @author Bart Malestein <bart@isset.nl>
 */
class Number implements ValueObjectInterface {


    protected $value;

    /**
     * @param $number
     * @throws \Hatchery\Builder\Exception\JobBuilderException
     */
    public function __construct($number)
    {

        if (false === is_numeric($number)) {
            throw new JobBuilderException('Number value must be numeric');
        }

        $this->value = intval($number);

    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }
}