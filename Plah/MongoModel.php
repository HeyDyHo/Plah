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
     * @return \MongoDB\Client
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

            self::$_client = new \MongoDB\Client($mongodb_string);
        }

        return self::$_client;
    }

    /**
     * Get a Mongo database.
     *
     * @return \MongoDB\Database
     */
    public static function getDb()
    {
        if (!isset(self::$_dbs[static::$_db])) {
            self::$_dbs[static::$_db] = self::getClient()->selectDatabase(static::$_db);
        }

        return self::$_dbs[static::$_db];
    }

    /**
     * Get a Mongo collection.
     *
     * @return \MongoDB\Collection
     */
    public static function getCollection()
    {
        $collection_name = static::$_db . '_' . static::$_collection;

        if (!isset(self::$_collections[$collection_name])) {
            self::$_collections[$collection_name] = self::getClient()->selectDatabase(static::$_db)->selectCollection(static::$_collection);
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
            $data = self::getCollection()->findOne(array(static::$_key => $value), array('typeMap' => array('array' => 'array', 'document' => 'array', 'root' => 'array')));

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
        $result = null;

        if (property_exists($this, '_id') && isset($this->_id) && !is_null($this->_id)) {
            $result = self::getCollection()->replaceOne(array('_id' => $this->_id), $this);
        } else {
            $result = self::getCollection()->insertOne($this);
            $this->_id = $result->getInsertedId();
        }

        return !!$result;
    }

    /**
     * Remove record.
     *
     * @return bool
     */
    public function remove()
    {
        $result = self::getCollection()->deleteOne(get_object_vars($this));

        return !!$result;
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

        $options = array();

        if (!empty($fields)) {
            $options['projection'] = $fields;
        }
        if (!empty($sort)) {
            $options['sort'] = $sort;
        }
        if (!is_null($skip)) {
            $options['skip'] = (int)$skip;
        }
        if (!is_null($limit)) {
            $options['limit'] = (int)$limit;
        }

        $options['typeMap'] = array(
            'array' => 'array',
            'document' => 'array',
            'root' => 'array'
        );

        $result = self::getCollection()->find($query, $options);

        foreach ($result as $rec) {
            $doc = new static();
            $doc->mergeProperties($rec);
            $data[] = $doc;
        }

        $count = self::getCollection()->count($query);
        $found = count($data);

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

        if (!empty($fields) && !isset($options['projection'])) {
            $options['projection'] = $fields;
        }

        if (!isset($options['typeMap'])) {
            $options['typeMap'] = array(
                'array' => 'array',
                'document' => 'array',
                'root' => 'array'
            );
        }

        $rec = self::getCollection()->findOne($query, $options);

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
        $options = array();

        if (!is_null($skip)) {
            $options['skip'] = (int)$skip;
        }
        if (!is_null($limit)) {
            $options['limit'] = (int)$limit;
        }

        return self::getCollection()->count($query, $options);
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
