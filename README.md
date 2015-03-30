DESCRIPTION
===========
This is the API client for Hatchery Video Transcoding API. Use this to simplify the use of the API in PHP.

Example
=======

The API requires a consumer and private key which can be requested at info@video-transcoder.com. 
The client will store the API token in the 'token_cache_location', so make sure this directory exists and is writable. 

    <?php

    include '../src/Hatchery/Autoloader.php';
    $client  = new Hatchery\Client('api_url', 'api_consumer_key', 'api_private_key', 'token_cache_location');
    $payload = $client->createJobAddPayload('preset', 'input_location', 'output_location');
    $client->sendPayload($payload);
