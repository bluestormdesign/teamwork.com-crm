<?php

namespace BluestormDesign\TeamworkCrm;

class Auth
{
    private static $config = [
        'url' => null,
        'key' => null
    ];

    public static function set($url, $key)
    {
        self::$config['url'] = $url;
        self::$config['key'] = $key;
    }

    public static function get()
    {
        return array_values(self::$config);
    }
}
