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
use Hatchery\Connection\Curl\CurlSubmit;

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

    /**
     * @param $api
     * @param $token
     * @param ConnectionInterface $connectionInterface
     */
    public function __construct($api, $token, ConnectionInterface $connectionInterface = null)
    {
        $this->token = $token;
        $this->baseLink = rtrim($api, '/');

        if ($connectionInterface === null) {
            $this->interface = new CurlSubmit();
        }
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
    public function submitRawJob(array $data)
    {
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
    private function handlePayload(Payload $payload)
    {
        /* @var $response \Hatchery\Connection\ResponseInterface */
        $response = $this->interface->sendPayload($payload);
        try {
            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                return $response;
            } else if ($response->getStatusCode() == 401 || $response->getStatusCode() == 403) {
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
     * @return bool
     * @throws Exception
     */
    public function getToken()
    {
        if ($this->token) {
            return $this->token;
        } else {
            throw new Exception("No token available");
        }
    }

    /**
     * @param $location
     * @return Connection\ResponseInterface
     * @throws Connection\ResponseException
     */
    public function getStatus($location)
    {
        $payload = new JobStatus($this->baseLink, $location);
        try {
            $payload->setHeader('x-auth-token', $this->getToken());
        } catch (Exception $ex) {
            $ex = new Connection\ResponseException('Unable to acquire login token: ' . $ex->getMessage());
            throw $ex;
        }
        $payload->setHeader('Content-Type', 'application/json');
        return $this->handlePayload($payload);
    }
}
