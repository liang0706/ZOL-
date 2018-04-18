<?php

/*
|---------------------------------------------------------------
| 数据存储类
|---------------------------------------------------------------
| @package ZOL
|
*/

class ZOL_Registry
{
    protected static $_aProps = array();

    /*
    |---------------------------------------------------------------
    | Constructor is disabled to enforce static usage.
    |---------------------------------------------------------------
    |
    */
    final private function __construct() {}

    /*
    |---------------------------------------------------------------
    | 获取已经存储的数据...
    |---------------------------------------------------------------
    | @param string $key
    | @return object
    | @todo make it work like Config
    */
    public static function get($key)
    {
        if (!self::exists($key)) {

            //return false;
            throw new ZOL_Exception("No entry is registered for key: $key");
        }
        return self::$_aProps[$key];
    }

    /*
    |---------------------------------------------------------------
    | Register a new object.
    |---------------------------------------------------------------
    | @param string $key
    | @param object $obj
    */
    public static function set($key, $obj)
    {
        if (self::exists($key)) {
            throw new ZOL_Exception("An entry is already registered for key: $key");
        }
        ZOL_Registry::$_aProps[$key] = $obj;
    }

    public static function exists($key)
    {
        return ! empty(self::$_aProps[$key]);
    }

    public static function reset()
    {
        self::$_aProps = null;
    }
}
