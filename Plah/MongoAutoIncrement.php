<?php

namespace Plah;

class MongoAutoIncrement extends MongoModel
{
    //Basic database settings
    protected static $_db = null;
    protected static $_collection = null;
    protected static $_key = 'key';

    //Model properties
    public $_id = null;
    public $key = '';
    public $value = 0;

    /**
     * Indexes.
     */
    public static function ensureIndexes()
    {
        self::_setDbCollection();
        self::getCollection()->ensureIndex(array('key' => 1), array('background' => true, 'unique' => true));
    }

    /**
     * Set database and collection.
     */
    private static function _setDbCollection()
    {
        self::$_db = Config::getInstance()->get('mongoautoincrement.db', Plah::getConfig('mongoautoincrement.db'));
        self::$_collection = Config::getInstance()->get('mongoautoincrement.collection', Plah::getConfig('mongoautoincrement.collection'));
    }

    /**
     * Initialize MongoAutoIncrement instance.
     *
     * @param mixed $value
     * @throws \Exception
     */
    public function __construct($value = null)
    {
        self::_setDbCollection();
        parent::__construct($value);
    }

    /**
     * Get the next auto increment value for a key.
     *
     * @param string $key
     * @param int $init_value
     * @return int
     */
    public function get($key, $init_value = 1)
    {
        $auto_increment = self::getCollection()->findAndModify(array('key' => $key), array('$inc' => array('value' => 1)), array('value' => true), array('new' => true));

        if (!empty($auto_increment)) {
            return (int)$auto_increment['value'];
        } else {
            $auto_increment = new self();
            $auto_increment->key = $key;
            $auto_increment->value = (int)$init_value;
            try {
                $auto_increment->save();
            } catch (\Exception $e) {
            }

            return $auto_increment->value;
        }
    }
}
