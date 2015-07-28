<?php
namespace Plah;

class Language extends Singleton
{
    private static $_config = array(  //Class config
        'dir' => '../language',
        'file_default' => 'en'
    );
    private static $_items = array();  //Language items

    private $_language = null;  //Currently selected language

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
     * Initialize language instance.
     */
    public function __construct()
    {
        $this->_language = self::$_config['file_default'];
    }

    /**
     * Get language item.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (!isset(self::$_items[$this->_language])) {
            $this->_parse($this->_language);
        }

        return isset(self::$_items[$this->_language][$key]) ? self::$_items[$this->_language][$key] : $default;
    }

    /**
     * Get all language items.
     *
     * @return array
     */
    public function getAll()
    {
        if (!isset(self::$_items[$this->_language])) {
            $this->_parse($this->_language);
        }

        return self::$_items[$this->_language];
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
        $language_default = rtrim(self::$_config['dir'], '/') . '/' . self::$_config['file_default'] . '.ini';
        self::$_items[$language] = IniParser::getInstance()->get($language_default);

        //Parse additional language file and update/add keys of default language
        if ($language != self::$_config['file_default']) {
            $language_additional = rtrim(self::$_config['dir'], '/') . '/' . $language . '.ini';
            self::$_items[$language] = array_merge(self::$_items[$language], IniParser::getInstance()->get($language_additional));
        }
    }
}
