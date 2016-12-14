<?php

namespace WCMD;

class Config {
    const CONFIG_PATH = '../config.ini';

    private static $config;
    
    public static function getConfig() {
        if (!self::$config) {
            self::$config = parse_ini_file(self::CONFIG_PATH);
        }
        return self::$config;
    }
    
    public static function get(string $key) {
        $config = self::getConfig();
        return $config[$key];
    }
}