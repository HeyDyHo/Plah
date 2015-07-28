<?php
namespace Plah;

abstract class Singleton
{
    private static $_instances = array();  //Singleton instances, one per class

    /**
     * Get a static instance of the class.
     *
     * @return static
     */
    public static function getInstance()
    {
        $class = get_called_class();

        if (!isset(self::$_instances[$class])) {
            $rc = new \ReflectionClass($class);
            self::$_instances[$class] = $rc->newInstanceArgs(func_get_args());
        }

        return self::$_instances[$class];
    }
}
