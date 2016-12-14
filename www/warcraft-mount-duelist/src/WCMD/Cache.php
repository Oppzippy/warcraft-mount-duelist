<?php

namespace WCMD;

use Memcached;

class Cache {
    private static $memcached;
    private static $memcached_servers = [
        ['127.0.0.1', 11211]
    ];
    
    public static function getMemcachedInstance() {
        if (!self::$memcached) {
            self::$memcached = new Memcached();
            self::$memcached->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
            self::$memcached->addServers(self::$memcached_servers);
        }
        return self::$memcached;
    }
    
    /**
        Retrieves a value from the cache.
        If it is not cached, $val_func is executed, cached, and returned.
        @param $key Key to retrieve or add to the cache
        @param $val_func Function to obtain the data that should otherwise be in the cache
        @param $expiration Cache expiration time
    */
    public static function cache(string $key, callable $val_func, int $expiration) {
        $mem = self::getMemcachedInstance();
        
        $result = $mem->get($key);
        if (!$result) {
            $result = $val_func();
            $mem->set($key, $result, $expiration);
        }
        
        return $result;
    }
}