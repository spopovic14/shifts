<?php

namespace App\Service\Storage;

use Symfony\Component\HttpFoundation\Session\SessionInterface as Session;

// TODO: Use cache instead of session? THe same client is always used, no need for separate tokens for sessions
/**
 * Uses a session for storing data
 */
class SessionStorage implements StorageInterface
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function get($key)
    {
        return $this->session->get($key);
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->session->set($key, $value);
    }

}