<?php

namespace BluestormDesign\TeamworkCrm\Helpers;

// from https://github.com/laravel/framework/blob/master/src/Illuminate/Support/Str.php

class Str
{
    /**
     * Convert a value to camel case.
     *
     * @param  string  $value
     * @return string
     */
    public static function camel($value)
    {
        return lcfirst(static::studly($value));
    }

    /**
     * Convert a value to studly caps case.
     *
     * @param  string  $value
     * @return string
     */
    public static function studly($value)
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $value)));
    }

    /**
     * Convert all underscores into dashes
     *
     * @param string  $value
     * @return string
     */
    public static function dash($value)
    {
        return str_replace('_', '-', $value);
    }
}
