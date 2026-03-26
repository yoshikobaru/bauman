<?php

namespace Artamonov\Rest\Foundation;


class Cache
{
    private static $_instance;
    private static $_cache;

    public function get($id, $ttl = 86400, $dir = false)
    {
        return self::$_cache->initCache($ttl, $id, $dir) ? self::$_cache->getVars() : false;
    }

    public function set($data)
    {
        if (self::$_cache->startDataCache()) {
            self::$_cache->endDataCache($data);
        }
    }

    public function clear($dir)
    {
        BXClearCache(true, $dir);
    }

    public static function getInstance(): Cache
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
            self::$_cache = \Bitrix\Main\Data\Cache::createInstance();
        }
        return self::$_instance;
    }

    private function __construct()
    {
    }

    public function __call($name, $arguments)
    {
        response()->internalServerError('Method \'' . $name . '\' is not defined');
    }

    private function __clone()
    {
    }
}
