<?php

namespace Hatchery\Builder;

/**
 * Class Job
 * @package Hatchery\Builder
 * @author Bart Malestein <bart@isset.nl>
 */
class Job implements ParsableInterface
{
    /**
     * @var Source[]
     */
    protected $inputs;

    /**
     * @var Source[]
     */
    protected $outputs;

    /**
     * @var string|null
     */
    protected $callback;

    /**
     * @var bool
     */
    protected $strict = true;


    /**
     * @var string|null
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;
    }

    /**
     * @var bool
     */
    public function setStrict($strict)
    {
        $this->strict = $strict;
    }

    /**
     * @param Source $source
     */
    public function add(Source $source)
    {
        if ($source instanceof Input) {
            $this->inputs[] = $source;
        } else {
            $this->outputs[] = $source;
        }
    }

    public function parse()
    {
        $jobs = [
            'inputs' => [],
            'outputs' => [],
        ];

        foreach ($this->inputs as $input) {
            $jobs['inputs'][] = $input->parse();
        }

        foreach ($this->outputs as $output) {
            $jobs['outputs'][] = $output->parse();
        }

        if ($this->callback !== null) {
            $jobs['callback'] = $this->callback;
        }

        return $jobs;
    }
}
