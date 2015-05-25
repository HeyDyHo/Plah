<?php

namespace Plah;

class Language extends Singleton
{
    private static $_items = array();  //Language items

    private $_language = null;  //Currently selected language

    /**
     * Initialize language instance.
     */
    public function __construct()
    {
        $this->_language = Config::getInstance()->get('language.file.default', Plah::getConfig('language.file.default'));
    }

    /**
     * Get language item.
     *
     * @param string $id
     * @param mixed $default
     * @return mixed
     */
    public function get($id, $default = null)
    {
        if (!isset(self::$_items[$this->_language])) {
            $this->_parse($this->_language);
        }

        return isset(self::$_items[$this->_language][$id]) ? self::$_items[$this->_language][$id] : $default;
    }

    /**
     * Set selected language.
     *
     * @param string $language
     */
    public function set($language)
    {
        $this->_language = $language;
    }

    /**
     * Parse language files.
     *
     * @param string $language
     */
    private function _parse($language)
    {
        //Parse default language file
        $language_default = rtrim(Config::getInstance()->get('language.dir', Plah::getConfig('language.dir')), '/') . '/' . Config::getInstance()->get('language.file.default', Plah::getConfig('language.file.default')) . '.ini';
        self::$_items[$language] = IniParser::getInstance()->parse($language_default);

        //Parse additional language file and update/add keys of default language
        if ($language != Config::getInstance()->get('language.file.default', Plah::getConfig('language.file.default'))) {
            $language_additional = rtrim(Config::getInstance()->get('language.dir', Plah::getConfig('language.dir')), '/') . '/' . $language . '.ini';
            self::$_items[$language] = array_merge(self::$_items[$language], IniParser::getInstance()->parse($language_additional));
        }
    }
}
