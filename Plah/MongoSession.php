<?php

namespace Plah;

class MongoSession extends MongoModel
{
    private static $_config = array(  //Class config
        'db' => 'session',
        'collection' => 'session',
        'name' => 'session',
        'expires' => 0
    );

    //Basic database settings
    protected static $_db = null;
    protected static $_collection = null;
    protected static $_key = 'id';

    //Model properties
    public $_id = null;
    public $id = '';

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
    public static function ensureIndexes()
    {
        self::_setDbCollection();
        self::getCollection()->ensureIndex(array('id' => 1), array('background' => true, 'unique' => true));
    }

    /**
     * Initialize MongoSession instance.
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
     * Start a session.
     * Load an existing session or create a new one.
     * The session is not saved to the database at
     * this point to prevent flooding the database
     * with empty sessions.
     */
    public function start()
    {
        //Convert expire time if necessary
        if (is_string(self::$_config['expires'])) {
            self::$_config['expires'] = strtotime(self::$_config['expires']);
            if (self::$_config['expires'] === false) {
                self::$_config['expires'] = 0;
            }
        }

        //Load session data if cookie exists or create a new session if no existing session data was found
        if (isset($_COOKIE[self::$_config['name']])) {
            $rec = self::getCollection()->findOne(array('id' => $_COOKIE[self::$_config['name']]));
            if (!is_null($rec)) {
                //Merge session values from record to current session
                $this->mergeProperties($rec);
            } else {
                //Use cookie value as session ID
                $this->id = $_COOKIE[self::$_config['name']];
            }

            //Extend cookie lifetime if necessary
            if (self::$_config['expires'] !== 0) {
                setcookie(self::$_config['name'], $this->id, self::$_config['expires'], '/');
            }
        } else {
            //Create new session ID and set a cookie
            $this->id = md5(uniqid('plah_session', true));
            setcookie(self::$_config['name'], $this->id, self::$_config['expires'], '/');
        }
    }

    /**
     * Set property value and save data.
     *
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        parent::set($key, $value);
        $this->save();
    }
}
