<?php

namespace Hatchery\Payload;

class JobAdd extends Payload
{
    public function __construct($url, $preset, $inputLocation, $outputLocation)
    {
        parent::__construct($url);

        $input = array();
        $input['url'] = $inputLocation;
        $input['reference'] = 'default_input';

        $output = array();
        $output['url'] = $outputLocation;
        $output['preset'] = $preset;
        $output['source'] = 'default_input';

        $this->data['input'] = $input;
        $this->data['output'] = $output;
    }

    /**
     * Set video output width x height
     * @param $width
     * @param $height
     */
    public function setOutputSize($width, $height)
    {
        $this->data['output']['width'] = $width;
        $this->data['output']['height'] = $height;
    }

    /**
     * Set offset of output video
     * @param $offset
     */
    public function addOffset($offset)
    {
        $this->data['output']['seek_offset'] = $offset;
    }

    /**
     * @param $duration
     */
    public function addDuration($duration)
    {
        $this->data['output']['output_length'] = $duration;
    }

    /**
     * Custom options, to be determined
     * @param $key
     * @param $value
     */
    public function addCustomOption($key, $value)
    {
        $this->data['output'][$key] = $value;
    }

    /**
     * Generate stills for output, location and filename required
     * optional amount, format, width and height
     * @param $outputLocation
     * @param $filename
     * @param int $amount
     * @param string $format
     * @param string $width
     * @param string $height
     */
    public function addStills($outputLocation, $filename, $amount = 5, $format = 'jpg', $width = '1920', $height = '1080')
    {
        $stills = array();
        $stills['base_url'] = $outputLocation;
        $stills['filename'] = $filename;

        $stills['amount'] = $amount;
        $stills['format'] = $format;

        $stills['width'] = $width;
        $stills['height'] = $height;

        $this->data['output']['stills'] = $stills;
    }
}
