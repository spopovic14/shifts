<?php

namespace App\Service\Storage;

interface StorageInterface
{
    /**
     * Get a value from storage by key
     *
     * @param string $key
     * @return mixed
     */
    public function get($key);

    /**
     * Save a value for key to storage
     *
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value);

}