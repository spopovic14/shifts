<?php

namespace App\Service;

use App\Exception\FailedAuthenticationException;
use App\Service\Storage\StorageInterface;
use GuzzleHttp\Client;
use \Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ResponseInterface;

class Authenticator
{
    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @param string $clientId
     * @param string $clientSecret
     * @param string $username
     * @param string $password
     * @param StorageInterface $storage
     */
    public function __construct($clientId, $clientSecret, $username, $password, StorageInterface $storage)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->username = $username;
        $this->password = $password;
        $this->storage = $storage;

        $this->client = new Client([
            'http_errors' => false,
        ]);
    }

    /**
     * Get an authentication token and store it
     *
     * @param bool $forceRefresh
     * @return array|mixed
     * @throws FailedAuthenticationException
     */
    public function getToken($forceRefresh = false)
    {
        $token = $this->storage->get('token');

        if(empty($token)) {
            $token = $this->requestToken();
        }

        if($forceRefresh || $this->isTokenExpired($token)) {
            $token = $this->refreshToken($token);
        }

        // Save token
        $this->storage->set('token', $token);

        return $token;
    }

    /**
     * @param array $token
     * @return array
     * @throws FailedAuthenticationException
     */
    private function refreshToken($token)
    {
        $response = $this->client->post('https://www.humanity.com/oauth2/token.php' ,[
            'form_params' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'refresh_token',
                'refresh_token' => $token['refresh_token'],
            ],
        ]);

        return $this->getTokenFromResponse($response);
    }

    /**
     * @return array
     * @throws FailedAuthenticationException
     */
    private function requestToken()
    {
        $response = $this->client->post('https://www.humanity.com/oauth2/token.php' ,[
            'form_params' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'password',
                'username' => $this->username,
                'password' => $this->password,
            ],
        ]);

        return $this->getTokenFromResponse($response);
    }

    /**
     * @param Response $response
     * @return array
     * @throws FailedAuthenticationException
     */
    private function getTokenFromResponse(Response $response)
    {
        // Check if request failed
        if($response->getStatusCode() !== 200) {
            $errorMessage = $this->getErrorFromResponse($response);

            $message = 'Get token request failed with status code %d and error: %s';

            throw new FailedAuthenticationException(sprintf($message, $response->getStatusCode(), $errorMessage));
        }

        $token = json_decode($response->getBody()->getContents(), true);

        // Check token format
        if(empty($token['access_token']) || empty($token['expires_in']) || empty($token['token_type']) || empty($token['refresh_token'])) {
            throw new FailedAuthenticationException('Bad token format returned');
        }

        // Calculate expiration date (subtract 10 seconds to compensate for request time)
        $expirationTimestamp = time() + $token['expires_in'] - 10;

        return [
            'access_token' => $token['access_token'],
            'token_type' => $token['token_type'],
            'refresh_token' => $token['refresh_token'],
            'expiration_timestamp' => $expirationTimestamp
        ];
    }

    /**
     * @param ResponseInterface $response
     * @return string
     */
    private function getErrorFromResponse($response)
    {
        $content = $response->getBody()->getContents();

        $data = json_decode($content, true);

        // Try to get a longer description first
        if(!empty($data['error_description'])) {
            return $data['error_description'];
        }

        // Get short error if there is no longer version
        if(!empty($data['error'])) {
            return $data['error'];
        }

        // No error message
        return 'No error message found';
    }

    /**
     * @param array $token
     * @return bool
     */
    private function isTokenExpired($token)
    {
        return $token['expiration_timestamp'] < time();
    }
}