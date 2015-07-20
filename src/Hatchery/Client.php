<?php

namespace Hatchery;

use Exception;
use Hatchery\Authentication\AuthenticationInterface;
use Hatchery\Authentication\KeyPairAuthentication;
use Hatchery\Authentication\TokenAuthentication;
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

    private $authentication;

    /**
     * @param $api
     * @param AuthenticationInterface $authenticationInterface
     * @param ConnectionInterface $connectionInterface
     * @throws Exception
     * @internal param null $consumerKey
     * @internal param null $privateKey
     * @internal param bool $tokenCacheLocation
     */
    public function __construct($api, AuthenticationInterface $authenticationInterface, ConnectionInterface $connectionInterface = null)
    {
        $this->baseLink = rtrim($api, '/');
        $this->$authentication = $authenticationInterface;

        if ($connectionInterface === null) {
            $this->interface = new CurlSubmit();
        }
    }

    /**
     * @param $token
     */
    public function setToken($token)
    {
        $this->token = $token;
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
        if ($this->authentication instanceof TokenAuthentication) {

            return $this->authentication->getToken();

        } else if ($this->authentication instanceof KeyPairAuthentication) {
            if ($this->token) {

                return $this->token;
            } else if (file_exists($this->authentication->getTokenPath())) {

                return file_get_contents($this->authentication->getTokenPath());
            } else {
                $loginPayload = new Login($this->baseLink . '/api/login', $this->authentication->getConsumerKey(), $this->authentication->getPrivateKey());

                $data = $this->sendPayload($loginPayload, false);
                $response = $data->getJsonResponse();

                $this->token = $response['token'];
                file_put_contents($this->authentication->getTokenPath(), $response['token']);

                return $response['token'];
            }
        }else{
            throw new Exception('Unable to retrieve token');
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
