<?php

namespace Plah;

class Config extends Singleton
{
    private static $_config = array(  //Class config
        'dir' => '../config',
        'file_default' => 'config-default',
        'file_local' => 'config-local'
    );
    private static $_items = array();  //Config items

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
     * Get config item.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (empty(self::$_items)) {
            $this->_parse();
        }

        return isset(self::$_items[$key]) ? self::$_items[$key] : $default;
    }

    /**
     * Parse config files.
     */
    private function _parse()
    {
        //Parse default config file
        $config_default = rtrim(self::$_config['dir'], '/') . '/' . self::$_config['file_default'] . '.ini';
        self::$_items = IniParser::getInstance()->get($config_default);

        //Parse local config file and update/add keys of default config
        $config_local = rtrim(self::$_config['dir'], '/') . '/' . self::$_config['file_local'] . '.ini';
        self::$_items = array_merge(self::$_items, IniParser::getInstance()->get($config_local));
    }
}
