<?php

namespace Hatchery;

use Exception;
use Hatchery\Authentication\AuthenticationInterface;
use Hatchery\Authentication\KeyPairAuthentication;
use Hatchery\Authentication\TokenAuthentication;
use Hatchery\Builder\Job;
use Hatchery\Connection\ConnectionInterface;
use Hatchery\Connection\ResponseException;
use Hatchery\Connection\ResponseInterface;
use Hatchery\Connection\UnauthorizedException;
use Hatchery\Connection\InsufficientFundsException;
use Hatchery\Connection\StrictWarningException;
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
     */
    public function __construct($api, AuthenticationInterface $authenticationInterface, ConnectionInterface $connectionInterface = null)
    {
        $this->baseLink = rtrim($api, '/');
        $this->authentication = $authenticationInterface;

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
     *
     * @return ResponseInterface
     *
     * @throws UnauthorizedException
     * @throws InsufficientFundsException
     * @throws ResponseException
     * @throws StrictWarningException
     * @throws Exception
     */
    private function handlePayload(Payload $payload): ResponseInterface
    {
        /* @var $response Connection\ResponseInterface */
        $response = $this->interface->sendPayload($payload);
        $data = json_decode($response->getContent(), true);
        $statusCode = $response->getStatusCode();
        if ($statusCode >= 200 && $statusCode < 300) {
            return $response;
        }
        if ($statusCode === 400 && isset($data['error'], $data['warnings'])) {
            $ex = new Connection\StrictWarningException($data['error']);
            $ex->setWarnings($data['warnings']);
            throw $ex;
        }
        if ($statusCode === 401 || $statusCode === 403) {
            $this->token = false;
            $ex = new Connection\UnauthorizedException(sprintf('[%s]: Unable to process request: [%s]', $statusCode, $response->getContent()));
            $ex->setResponse($response);
            throw $ex;
        }
        if ($statusCode === 402) {
            throw new Connection\InsufficientFundsException('Not enough credits to process request');
        }

        $ex = new Connection\ResponseException(sprintf('[%s]: Unexpected response: [%s]', $response->getStatusCode(), $response->getContent()));
        $ex->setResponse($response);
        throw $ex;
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
        } else {
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

    /**
     * @param $actionType
     * @return Connection\ResponseInterface
     * @throws Connection\ResponseException
     * @throws Connection\StrictWarningException
     * @throws Exception
     */
    public function getPresets($actionType = null)
    {
        if ($actionType !== null) {
            $payload = new Payload($this->baseLink . '/api/presets?actionType=' . $actionType);
        } else {
            $payload = new Payload($this->baseLink . '/api/presets');
        }

        try {
            $payload->setHeader('x-auth-token', $this->getToken());
        } catch (Exception $ex) {
            $ex = new Connection\ResponseException('Unable to acquire login token: ' . $ex->getMessage());
            throw $ex;
        }
        $payload->setMethod('get');
        $payload->setHeader('Content-Type', 'application/json');
        return $this->handlePayload($payload);
    }

    /**
     * @return Connection\ResponseInterface
     * @throws Connection\ResponseException
     * @throws Connection\StrictWarningException
     * @throws Exception
     */
    public function getTemplates()
    {
        $payload = new Payload($this->baseLink . '/api/job_templates');

        try {
            $payload->setHeader('x-auth-token', $this->getToken());
        } catch (Exception $ex) {
            $ex = new Connection\ResponseException('Unable to acquire login token: ' . $ex->getMessage());
            throw $ex;
        }
        $payload->setMethod('get');
        $payload->setHeader('Content-Type', 'application/json');
        return $this->handlePayload($payload);
    }

    /**
     * @param $id
     * @return Connection\ResponseInterface
     * @throws Connection\ResponseException
     * @throws Connection\StrictWarningException
     * @throws Exception
     */
    public function getTemplate($id)
    {
        $payload = new Payload($this->baseLink . '/api/job_templates/' . $id);

        try {
            $payload->setHeader('x-auth-token', $this->getToken());
        } catch (Exception $ex) {
            $ex = new Connection\ResponseException('Unable to acquire login token: ' . $ex->getMessage());
            throw $ex;
        }
        $payload->setMethod('get');
        $payload->setHeader('Content-Type', 'application/json');
        return $this->handlePayload($payload);
    }
}
