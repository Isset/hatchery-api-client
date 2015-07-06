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

        return $jobs;
    }
}
