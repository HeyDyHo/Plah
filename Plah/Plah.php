<?php

namespace Plah;

class Plah
{
    private static $_config = array(
        'config.dir' => '../config',
        'config.file.default' => 'config-default',
        'config.file.local' => 'config-local',
        'platform.dir' => '../platform',
        'platform.file.default' => 'platform-default',
        'platform.file.local' => 'platform-local',
        'language.dir' => '../language',
        'language.file.default' => 'en',
        'mongodb.host' => 'localhost',
        'mongodb.port' => '',
        'mongodb.user' => '',
        'mongodb.password' => '',
        'mongodb.db' => '',
        'mongoautoincrement.db' => 'autoincrement',
        'mongoautoincrement.collection' => 'autoincrement'
    );

    /**
     * Initialize Plah.
     *
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        //Set config values
        self::$_config = array_merge(self::$_config, $config);
    }

    /**
     * Get config item.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getConfig($key, $default = null)
    {
        return isset(self::$_config[$key]) ? self::$_config[$key] : $default;
    }

    /**
     * Set config item(s).
     *
     * @param array|string $key
     * @param mixed $value
     */
    public static function setConfig($key, $value = null)
    {
        if (is_array($key)) {
            self::$_config = array_merge(self::$_config, $key);
        } else {
            self::$_config[$key] = $value;
        }
    }
}
