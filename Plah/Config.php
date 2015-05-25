<?php

namespace Plah;

class Config extends Singleton
{
    private static $_items = array();  //Config items

    /**
     * Get config item.
     *
     * @param string $id
     * @param mixed $default
     * @return mixed
     */
    public function get($id, $default = null)
    {
        if (empty(self::$_items)) {
            $this->_parse();
        }

        return isset(self::$_items[$id]) ? self::$_items[$id] : $default;
    }

    /**
     * Parse config files.
     */
    private function _parse()
    {
        //Parse default config file
        $config_default = rtrim(Plah::getConfig('config.dir'), '/') . '/' . Plah::getConfig('config.file.default') . '.ini';
        self::$_items = IniParser::getInstance()->parse($config_default);

        //Parse local config file and update/add keys of default config
        $config_local = rtrim(Plah::getConfig('config.dir'), '/') . '/' . Plah::getConfig('config.file.local') . '.ini';
        self::$_items = array_merge(self::$_items, IniParser::getInstance()->parse($config_local));
    }
}
