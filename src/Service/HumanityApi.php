<?php

namespace App\Service;

use App\Exception\BadApiResponseException;
use GuzzleHttp\Client;

class HumanityApi
{
    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var Authenticator
     */
    private $authenticator;

    /**
     * @var Client
     */
    private $client;

    /**
     * @param string $baseUrl
     * @param Authenticator $authenticator
     */
    public function __construct($baseUrl, Authenticator $authenticator)
    {
        $this->baseUrl = $baseUrl;
        $this->authenticator = $authenticator;

        $this->client = new Client([
            'http_errors' => false,
        ]);
    }

    /**
     * Get a json response from Humanity API. Tries to refresh the access token if it expired.
     * Throws an exception if the response isn't valid json or doesn't have a 'status' field,
     * returns the error if it fails otherwise.
     *
     * @param string $method
     * @param string $url
     * @param array $parameters
     * @param bool $tokenRefreshed - should not be used when calling this function from the outside
     * @return array
     * @throws \App\Exception\FailedAuthenticationException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws BadApiResponseException
     */
    public function getJsonResponse($method, $url, $parameters = [], $tokenRefreshed = false)
    {
        $response = $this->sendRequest($method, $url, $parameters);

        $data =  json_decode($response->getBody()->getContents(), true);

        if(empty($data['status'])) {
            throw new BadApiResponseException('Humanity API returned no status');
        }

        // Check if there is a token error, but only if the token wasn't already refreshed
        if(!$tokenRefreshed && $data['status'] === 3 && $data['data'] === 'Invalid token key - Please re-authenticate') {
            // Force a token refresh
            $this->authenticator->getToken(true);

            // Try again
            return $this->getJsonResponse($method, $url, $parameters, true);
        }

        return $data;
    }

    /**
     * Send a request to Humanity API and return the response object.
     * Automatically handles access token creation and refreshing.
     *
     * @param string $method
     * @param string $url
     * @param array $parameters
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \App\Exception\FailedAuthenticationException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendRequest($method, $url, $parameters = [])
    {
        $url = $this->baseUrl . $url;
        $token = $this->authenticator->getToken();

        // Add token to query
        $parameters['query']['access_token'] = $token['access_token'];

        $response = $this->client->request($method, $url, $parameters);

        // If the server returned 401 or 403, try to refresh the token and try again
        if($response->getStatusCode() === 401 || $response->getStatusCode() === 403) {
            $token = $this->authenticator->getToken(true);

            // Add new token to query
            $parameters['query']['access_token'] = $token['access_token'];

            $response = $this->client->request($method, $url, $parameters);
        }

        return $response;
    }
}