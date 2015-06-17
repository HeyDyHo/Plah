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
     * Initialize basic config.
     *
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        self::$_config = array_merge(self::$_config, $config);
    }

    /**
     * Get config item.
     *
     * @param string $id
     * @param mixed $default
     * @return mixed
     */
    public static function getConfig($id, $default = null)
    {
        return isset(self::$_config[$id]) ? self::$_config[$id] : $default;
    }
}
