<?php
namespace Plah;

class MongoAutoIncrement extends MongoModel
{
    private static $_config = array(  //Class config
        'db' => 'autoincrement',
        'collection' => 'autoincrement'
    );

    //Basic database settings
    protected static $_db = null;
    protected static $_collection = null;
    protected static $_key = 'key';

    //Model properties
    public $_id = null;
    public $key = '';
    public $value = 0;

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
     * Set database and collection.
     */
    private static function _setDbCollection()
    {
        self::$_db = self::$_config['db'];
        self::$_collection = self::$_config['collection'];
    }

    /**
     * Indexes.
     */
    public static function createIndexes()
    {
        self::_setDbCollection();
        self::getCollection()->createIndex(array('key' => 1), array('background' => true, 'unique' => true));
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
    public function getNext($key, $init_value = 1)
    {
        $auto_increment = self::getCollection()->findOneAndUpdate(array('key' => $key), array('$inc' => array('value' => 1)), array('projection' => array('value' => true), 'returnDocument' => \MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER, 'typeMap' => array('array' => 'array', 'document' => 'array', 'root' => 'array')));

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
