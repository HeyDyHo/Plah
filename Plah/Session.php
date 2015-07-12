<?php

namespace Plah;

class Session extends Singleton
{
    /**
     * Get session value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    /**
     * Set session value for key.
     *
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Delete session value by key.
     *
     * @param string $key
     */
    public function delete($key)
    {
        unset($_SESSION[$key]);
    }
}
