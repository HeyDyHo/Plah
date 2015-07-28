<?php
namespace Plah;

abstract class MongoModel extends Singleton
{
    private static $_config = array(  //Class config
        'host' => 'localhost',
        'port' => '',
        'user' => '',
        'password' => '',
        'auth_db' => ''
    );
    private static $_client = null;  //Mongo client instance, one for all
    private static $_dbs = array();  //Mondo db instances, one per db
    private static $_collections = array();  //Mongo collection instances, one per collection

    protected static $_db = null;  //Database for the model
    protected static $_collection = null;  //Collection for the model
    protected static $_key = null;  //Primary key for the model, used for quick finding one record

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
     * Get a Mongo client.
     *
     * @return \MongoClient
     */
    public static function getClient()
    {
        if (is_null(self::$_client)) {
            $mongodb_string = 'mongodb://';

            if (!empty(self::$_config['user']) && !empty(self::$_config['password'])) {
                $mongodb_string .= self::$_config['user'] . ':' . self::$_config['password'] . '@';
            }

            $mongodb_string .= self::$_config['host'];

            if (!empty(self::$_config['port'])) {
                $mongodb_string .= ':' . self::$_config['port'];
            }

            if (!empty(self::$_config['auth_db'])) {
                $mongodb_string .= '/' . self::$_config['auth_db'];
            }

            self::$_client = new \MongoClient($mongodb_string);
        }

        return self::$_client;
    }

    /**
     * Get a Mongo database.
     *
     * @return \MongoDB
     */
    public static function getDb()
    {
        if (!isset(self::$_dbs[static::$_db])) {
            self::$_dbs[static::$_db] = self::getClient()->selectDB(static::$_db);
        }

        return self::$_dbs[static::$_db];
    }

    /**
     * Get a Mongo collection.
     *
     * @return \MongoCollection
     */
    public static function getCollection()
    {
        $collection_name = static::$_db . '_' . static::$_collection;

        if (!isset(self::$_collections[$collection_name])) {
            self::$_collections[$collection_name] = self::getClient()->selectCollection(static::$_db, static::$_collection);
        }

        return self::$_collections[$collection_name];
    }

    /**
     * Initialize model instance.
     *
     * @param mixed $value
     * @throws \Exception
     */
    public function __construct($value = null)
    {
        unset($this->_id);

        if (!is_null($value) && !is_null(static::$_key)) {
            $data = self::getCollection()->findOne(array(static::$_key => $value));

            if (!is_null($data)) {
                $this->mergeProperties($data);
            } else {
                throw new \Exception('No data found: ' . $value);
            }
        }
    }

    /**
     * Merge array data to existing properties.
     *
     * @param array $data
     */
    public function mergeProperties(array $data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Safe merge array data to existing properties.
     *
     * @param array $data
     */
    public function mergePropertiesSafe(array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Get property value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return property_exists($this, $key) ? $this->$key : $default;
    }

    /**
     * Set property value.
     *
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->$key = $value;
    }

    /**
     * Save record.
     *
     * @return bool
     */
    public function save()
    {
        return self::getCollection()->save($this);
    }

    /**
     * Remove record.
     *
     * @return bool
     */
    public function remove()
    {
        return self::getCollection()->remove(get_object_vars($this));
    }

    /**
     * Find records.
     *
     * @param array $query
     * @param array $fields
     * @param array $sort
     * @param null|int $skip
     * @param null|int $limit
     * @param int $count
     * @param int $found
     * @return static[]
     */
    public function find(array $query = array(), array $fields = array(), array $sort = array(), $skip = null, $limit = null, &$count = 0, &$found = 0)
    {
        $data = array();

        $result = self::getCollection()->find($query, $fields);

        if (!empty($sort)) {
            $result->sort($sort);
        }
        if (!is_null($skip)) {
            $result->skip((int)$skip);
        }
        if (!is_null($limit)) {
            $result->limit((int)$limit);
        }

        $count = $result->count();
        $found = $result->count(true);

        foreach ($result as $rec) {
            $doc = new static();
            $doc->mergeProperties($rec);
            $data[] = $doc;
        }

        return $data;
    }

    /**
     * Find one record.
     *
     * @param array $query
     * @param array $fields
     * @param array $options
     * @return null|static
     */
    public function findOne(array $query = array(), array $fields = array(), array $options = array())
    {
        $data = null;

        $rec = self::getCollection()->findOne($query, $fields);

        if (!is_null($rec)) {
            $data = new static();
            $data->mergeProperties($rec);
        }

        return $data;
    }

    /**
     * Get the number of records.
     *
     * @param array $query
     * @param null|int $skip
     * @param null|int $limit
     * @return int
     */
    public function count(array $query = array(), $skip = null, $limit = null)
    {
        $result = self::getCollection()->find($query);

        if (!is_null($skip)) {
            $result->skip((int)$skip);
        }
        if (!is_null($limit)) {
            $result->limit((int)$limit);
        }

        return $result->count(true);
    }

    /**
     * Alias for count.
     *
     * @param array $query
     * @param null|int $skip
     * @param null|int $limit
     * @return int
     */
    public function found(array $query = array(), $skip = null, $limit = null)
    {
        return $this->count($query, $skip, $limit);
    }
}
