<?php

namespace Hatchery\Builder\ValueObjects;
use Hatchery\Builder\Exception\JobBuilderException;
use Hatchery\Builder\ParsableInterface;

/**
 * Class Timestamp
 * @package Hatchery\Builder\ValueObjects
 * @author Bart Malestein <bart@isset.nl>
 */
class Timestamp implements ValueObjectInterface
{

    /**
     * @var string
     */
    protected $value;

    /**
     * @param $timestamp
     * @throws \Hatchery\Builder\Exception\JobBuilderException
     */
    public function __construct($timestamp)
    {
        if (preg_match('/^([0-9]{1,3}:)?[0-5][0-9]:[0-5][0-9](\.[0-9]{1,3})?$/', $timestamp, $matches) === 1) {
        } else if (preg_match('/((^100)|(^[0-9]{1,2}))\%$/', $timestamp, $matches) === 1) {
        } else if (preg_match('/^([0-9]*)$/', $timestamp, $matches) === 1) {
        } else {
            throw new JobBuilderException('Invalid still timestamp: ' . $timestamp);
        }

        $this->value = $timestamp;
    }


    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}