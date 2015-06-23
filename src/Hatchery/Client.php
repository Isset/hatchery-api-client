<?php

namespace Hatchery;

use Exception;
use Hatchery\Builder\Job;
use Hatchery\Connection\ConnectionInterface;
use Hatchery\Payload\JobAdd;
use Hatchery\Payload\JobPayload;
use Hatchery\Payload\JobStatus;
use Hatchery\Payload\Payload;
use Hatchery\Payload\RawPayload;

/**
 * Class Client
 * @package Hatchery
 * @author Bart Malestein <bart@isset.nl>
 */
class Client
{

    private $baseLink;

    private $interface;

    private $token = false;


    private $loginPayload;

    /**
     * @param ConnectionInterface $connectionInterface
     * @param $api
     * @param $consumerKey
     * @param $privateKey
     * @param bool $tokenCacheLocation
     * @throws Exception
     */
    public function __construct(ConnectionInterface $connectionInterface, $api, $consumerKey, $privateKey, $tokenCacheLocation = false)
    {
        if (!$tokenCacheLocation) {
            $tokenCacheLocation = __DIR__ . '/../Cache/';
        }

        $tokenCacheLocation = rtrim($tokenCacheLocation, '/') . '/';
        if (!is_writable($tokenCacheLocation)) {
            throw new Exception('token cache location isn\'t writable: ' . $tokenCacheLocation);
        }

        $this->tokenPath = $tokenCacheLocation . $consumerKey . '-token';
        $this->baseLink = rtrim($api, '/');


        $this->interface = $connectionInterface;
        $this->loginPayload = new Login($this->baseLink . '/api/login', $consumerKey, $privateKey);
    }

    /**
     * @param $preset
     * @param $uriInput
     * @param $uriOutput
     * @return JobAdd
     * @deprecated - use submitJob instead
     */
    public function createJobAddPayload($preset, $uriInput, $uriOutput)
    {
        return new JobAdd($this->baseLink . '/api/jobs/', $preset, $uriInput, $uriOutput);
    }

    /**
     * @param $identifier
     * @return JobStatus
     */
    public function createJobStatusPayload($identifier)
    {
        return new JobStatus($this->baseLink, $identifier);
    }

    /**
     * @param Job $job
     * @return Connection\ResponseInterface
     * @throws Connection\ResponseException
     */
    public function submitJob(Job $job)
    {
        $payload = new JobPayload($this->baseLink . '/api/jobs/', $job);
        try {
            $payload->setHeader('x-auth-token', $this->getToken());
        } catch (Exception $ex) {
            $ex = new Connection\ResponseException('Unable to acquire login token: ' . $ex->getMessage());
            throw $ex;
        }
        $payload->setHeader('Content-Type', 'application/json');
        return $this->handlePayload($payload);
    }

    /**
     * @param $data
     * @return Connection\ResponseInterface
     * @throws Connection\ResponseException
     */
    public function submitRawJob(array $data){
        $payload = new RawPayload($this->baseLink . '/api/jobs/', $data);
        try {
            $payload->setHeader('x-auth-token', $this->getToken());
        } catch (Exception $ex) {
            $ex = new Connection\ResponseException('Unable to acquire login token: ' . $ex->getMessage());
            throw $ex;
        }
        $payload->setHeader('Content-Type', 'application/json');
        return $this->handlePayload($payload);
    }

    /**
     * @param Payload $payload
     * @param bool $addHeader
     * @return Connection\ResponseInterface
     * @throws Connection\ResponseException
     */
    public function sendPayload(Payload $payload, $addHeader = true)
    {
        if ($addHeader) {
            try {
                $payload->setHeader('x-auth-token', $this->getToken());
            } catch (Exception $ex) {
                $ex = new Connection\ResponseException('Unable to acquire login token.');
                throw $ex;
            }
        }
        $payload->setHeader('Content-Type', 'application/json');
        return $this->handlePayload($payload);
    }

    /**
     * @param Payload $payload
     * @return Connection\ResponseInterface
     * @throws Connection\ResponseException
     */
    private function handlePayload(Payload $payload){
        /* @var $response \Hatchery\Connection\ResponseInterface */
        $response = $this->interface->sendPayload($payload);
        try {

            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {

                return $response;
            } else if ($response->getStatusCode() == 401 || $response->getStatusCode() == 403) {
                if (file_exists($this->tokenPath)) {
                    unlink($this->tokenPath);
                }
                $this->token = false;

                $ex = new Connection\ResponseException(sprintf('[%s]: Unable to process request: [%s]', $response->getStatusCode(), $response->getContent()));
                $ex->setResponse($response);
                throw $ex;
            } else {

                $ex = new Connection\ResponseException(sprintf('[%s]: Unexpected response: [%s]', $response->getStatusCode(), $response->getContent()));
                $ex->setResponse($response);
                throw $ex;
            }
        } catch (Exception $ex) {
            $ex = new Connection\ResponseException($ex->getMessage());
            $ex->setResponse($response);
            throw $ex;
        }
    }

    /**
     * @return bool|string
     * @throws Connection\ResponseException
     */
    public function getToken()
    {
        if ($this->token) {
            return $this->token;
        }
        if (file_exists($this->tokenPath)) {
            return file_get_contents($this->tokenPath);
        }
        $data = $this->sendPayload($this->loginPayload, false);
        $response = $data->getJsonResponse();

        $this->token = $response['token'];
        file_put_contents($this->tokenPath, $response['token']);
        return $response['token'];
    }

}
