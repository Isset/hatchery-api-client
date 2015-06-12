<?php

namespace Hatchery\Tests\Builder;

use Hatchery\Builder\Caption;
use Hatchery\Builder\Input;
use Hatchery\Builder\Job;
use Hatchery\Builder\Output;
use Hatchery\Builder\Playlist;
use Hatchery\Builder\Stills;
use Hatchery\Builder\Url\Url;
use Hatchery\Builder\ValueObjects\Number;
use Hatchery\Builder\ValueObjects\Timestamp;
use Hatchery\Builder\Watermark;
use PHPUnit_Framework_TestCase;

/**
 * Class JobTest
 * @package Hatchery\Tests\Builder
 * @author Bart Malestein <bart@isset.nl>
 */
class JobTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     */
    public function testBasicJob()
    {
        $job = new Job();
        $input = new Input(new Url('ftp://domain.ftp.com/input/test/filename.mp4'));
        $output = new Output($input, new Url('ftp://domain.ftp.com/output/test/filename.mp4'));
        $output->setFilename('adjusted_filename.mp4');

        $job->add($input);
        $job->add($output);

        $result = $job->parse();

        $this->assertArrayHasKey('inputs', $result);
        $this->assertArrayHasKey('outputs', $result);

        $this->assertArrayHasKey('reference', $result['inputs'][0]);

        $this->assertArrayHasKey('reference', $result['outputs'][0]);
        $this->assertArrayHasKey('source', $result['outputs'][0]);

        $this->assertEquals('ftp://domain.ftp.com/input/test/filename.mp4', $result['inputs'][0]['url']);
        $this->assertEquals('ftp://domain.ftp.com/output/test/adjusted_filename.mp4', $result['outputs'][0]['url']);
    }

    /**
     * @throws \Hatchery\Builder\Exception\JobBuilderException
     */
    public function testSegmentedJob()
    {
        $job = new Job();
        $input = new Input(new Url('ftp://domain.ftp.com/input/test/filename.mp4'));
        $output = new Output($input, new Url('ftp://domain.ftp.com/output/test/filename.mp4'));
        $output->setSegmented();
        $playlist = new Playlist(new Url('ftp://domain.ftp.com/output/test/playlist.mp4'));
        $playlist->addSegmentedOutput($output);

        $job->add($input);
        $job->add($output);
        $job->add($playlist);

        $result = $job->parse();

        $this->assertArrayHasKey('inputs', $result);
        $this->assertArrayHasKey('outputs', $result);

        $this->assertEquals('segmented', $result['outputs'][0]['type']);
        $this->assertEquals('ftp://domain.ftp.com/output/test/playlist.mp4', $result['outputs'][1]['url']);
        $this->assertEquals($result['outputs'][0]['reference'], $result['outputs'][1]['streams'][0]);

        $this->assertEquals('playlist', $result['outputs'][1]['type']);

    }

    /**
     * @expectedException \Hatchery\Builder\Exception\JobBuilderException
     * @throws \Hatchery\Builder\Exception\JobBuilderException
     */
    public function testBrokenSegmentedJob()
    {
        $input = new Input(new Url('ftp://domain.ftp.com/input/test/filename.mp4'));
        $output = new Output($input, new Url('ftp://domain.ftp.com/output/test/filename.mp4'));
        $playlist = new Playlist(new Url('ftp://domain.ftp.com/output/test/playlist.mp4'));
        $playlist->addSegmentedOutput($output);
    }

    /**
     * @expectedException \Hatchery\Builder\Exception\JobBuilderException
     * @throws \Hatchery\Builder\Exception\JobBuilderException
     */
    public function testBrokenStillsJob()
    {
        $input = new Input(new Url('ftp://domain.ftp.com/input/test/filename.mp4'));
        $output = new Output($input, new Url('ftp://domain.ftp.com/output/test/filename.mp4'));

        $stills = new Stills(new Url('ftp://domain.ftp.com/output/test/'));
        $stills->setAmount(new Number(10));
        $stills->addTimestamp(new Timestamp('10:10:10.100'));
        $output->addStills($stills);
    }

    public function testOutputOptions()
    {
        $job = new Job();
        $input = new Input(new Url('ftp://domain.ftp.com/input/test/filename.mp4'));
        $output = new Output($input, new Url('ftp://domain.ftp.com/output/test/filename.mp4'));


        $stills = new Stills(new Url('ftp://domain.ftp.com/output/test/'));
        $stills->setAmount(new Number(10));
        $output->addStills($stills);

        $watermark = new Watermark(new Url('ftp://domain.ftp.com/input/watermark/watermark.png'));
        $output->addWatermark($watermark);

        $caption = new Caption(new Url('ftp://domain.ftp.com/input/caption/caption.ass'));
        $output->setCaption($caption);

        $output->setWidth(new Number(50));
        $output->setHeight(new Number(10));
        $output->setDeinterlaced();
        $output->setOffset(new Timestamp('10:10:10.000'));
        $output->setOutputLength(new Number(60));

        $job->add($input);
        $job->add($output);

        $result = $job->parse();

        $this->assertEquals($result['outputs'][0]['caption_url'], 'ftp://domain.ftp.com/input/caption/caption.ass');

        $this->assertEquals($result['outputs'][0]['seek_offset'], '10:10:10.000');
        $this->assertEquals($result['outputs'][0]['output_length'], 60);
        $this->assertEquals($result['outputs'][0]['width'], 50);
        $this->assertEquals($result['outputs'][0]['height'], 10);
        $this->assertEquals($result['outputs'][0]['deinterlace'], true);

        $this->assertEquals($result['outputs'][0]['stills'][0]['base_url'], 'ftp://domain.ftp.com/output/test/');
        $this->assertEquals($result['outputs'][0]['stills'][0]['amount'], 10);

        $this->assertEquals($result['outputs'][0]['watermarks'][0]['url'], 'ftp://domain.ftp.com/input/watermark/watermark.png');
    }

}