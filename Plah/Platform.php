<?php

namespace Plah;

class Platform extends Singleton
{
    private static $_items = array();  //Platform items

    private $_platform = null;  //Currently selected platform

    /**
     * Initialize platform instance.
     */
    public function __construct()
    {
        $this->_platform = Config::getInstance()->get('platform.file.default', Plah::getConfig('platform.file.default'));
    }

    /**
     * Get platform item.
     *
     * @param string $id
     * @param mixed $default
     * @return mixed
     */
    public function get($id, $default = null)
    {
        if (!isset(self::$_items[$this->_platform])) {
            $this->_parse($this->_platform);
        }

        return isset(self::$_items[$this->_platform][$id]) ? self::$_items[$this->_platform][$id] : $default;
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
        $platform_default = rtrim(Config::getInstance()->get('platform.dir', Plah::getConfig('platform.dir')), '/') . '/' . Config::getInstance()->get('platform.file.default', Plah::getConfig('platform.file.default')) . '.ini';
        self::$_items[$platform] = IniParser::getInstance()->parse($platform_default);

        //Parse local platform file and update/add keys of default platform
        $platform_local = rtrim(Plah::getConfig('platform.dir'), '/') . '/' . Plah::getConfig('platform.file.local') . '.ini';
        self::$_items[$platform] = array_merge(self::$_items[$platform], IniParser::getInstance()->parse($platform_local));

        //Parse additional platform file and update/add keys of default platform
        if ($platform != Config::getInstance()->get('platform.file.default', Plah::getConfig('platform.file.default'))) {
            $platform_additional = rtrim(Config::getInstance()->get('platform.dir', Plah::getConfig('platform.dir')), '/') . '/' . $platform . '.ini';
            self::$_items[$platform] = array_merge(self::$_items[$platform], IniParser::getInstance()->parse($platform_additional));
        }
    }
}
