<?php

namespace Plah;

class Platform extends Singleton
{
    private static $_config = array(  //Class config
        'dir' => '../platform',
        'file_default' => 'platform-default',
        'file_local' => 'platform-local'
    );
    private static $_items = array();  //Platform items

    private $_platform = null;  //Currently selected platform

    /**
     * Set config.
     *
     * @param array $config
     */
    public static function config(array $config)
    {
        self::$_config = array_merge(self::$_config, $config);
    }

    /**
     * Initialize platform instance.
     */
    public function __construct()
    {
        $this->_platform = self::$_config['file_default'];
    }

    /**
     * Get platform item.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (!isset(self::$_items[$this->_platform])) {
            $this->_parse($this->_platform);
        }

        return isset(self::$_items[$this->_platform][$key]) ? self::$_items[$this->_platform][$key] : $default;
    }

    /**
     * Get all platform items.
     *
     * @return array
     */
    public function getAll()
    {
        if (!isset(self::$_items[$this->_platform])) {
            $this->_parse($this->_platform);
        }

        return self::$_items[$this->_platform];
    }

    /**
     * Set selected platform.
     *
     * @param string $platform
     */
    public function set($platform)
    {
        $this->_platform = $platform;
    }

    /**
     * Parse platform files.
     *
     * @param string $platform
     */
    private function _parse($platform)
    {
        //Parse default platform file
        $platform_default = rtrim(self::$_config['dir'], '/') . '/' . self::$_config['file_default'] . '.ini';
        self::$_items[$platform] = IniParser::getInstance()->get($platform_default);

        //Parse local platform file and update/add keys of default platform
        $platform_local = rtrim(self::$_config['dir'], '/') . '/' . self::$_config['file_local'] . '.ini';
        self::$_items[$platform] = array_merge(self::$_items[$platform], IniParser::getInstance()->get($platform_local));

        //Parse additional platform file and update/add keys of default platform
        if ($platform != self::$_config['file_default']) {
            $platform_additional = rtrim(self::$_config['dir'], '/') . '/' . $platform . '.ini';
            self::$_items[$platform] = array_merge(self::$_items[$platform], IniParser::getInstance()->get($platform_additional));
        }
    }
}
