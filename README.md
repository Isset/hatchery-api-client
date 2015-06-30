DESCRIPTION
===========
This is the API client for http://my.videotranscoder.io/ API. Use this to simplify the use of the API in PHP.

Example
=======

The API requires a consumer and private key which can be requested at info@my.videotranscoder.io. 
The client will store the API token in the 'token_cache_location', so make sure this directory exists and is writable. 

    <?php

    include '../src/Hatchery/Autoloader.php';
    $client  = new Hatchery\Client('api_url', 'api_consumer_key', 'api_private_key', 'token_cache_location');
    
    //create a new job, this class will contain all inputs and outputs
    $job = new Hatchery\Builder\Job();
    
    //create a new input, this class has to contain a valid URL and can be used to create outputs
    $input = new Hatchery\Builder\Input(new Hatchery\Builder\Url\Url('ftp://my_ftp_in_location.com/folder/input_file.mp4'));
    
    //create a new output, which links to a specific input and requires an output URL
    //this class also opens up some methods to manipulate your output
    $output = new Hatchery\Builder\Output($input, new Hatchery\Builder\Url\Url('ftp://my_ftp_out_location.com/folder/output_file.webm'));
    
    //use the name of own of your presets, or use on of the default video-transcoder presets
    $output->setPreset('My_own_webm_preset');
    
    //example of a number option, use the value objects to initiate
    $output->setOutputLength(new Hatchery\Builder\ValueObjects\Number(60));
    
    //example of creating stills
    $stills = new Stills(new new Hatchery\Builder\Url\Url('ftp://my_ftp_out_location.com/folder/my_stills_folder/''));
    $stills->setFilename('my_stills_frame_{{number}}');
    $stills->setAmount(new Hatchery\Builder\ValueObjects\Number(5));

    //add stills task to a specific output
    $output->addStills($stills);
    
    //add in- and outputs to job
    $job->add($input);
    $job->add($output);

    //submit job
    $response = $client->submitJob($job);
    
    //retrieve polling location (containing job id)
    $location = $response->getLocation();