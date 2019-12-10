<?php

namespace BluestormDesign\TeamworkCrm;

class Factory
{
    public static function build($className)
    {
        $className = str_replace(['/', '.'], '\\', $className);
        $className = preg_replace_callback('/(\\\.)/', function ($matches) {
            return strtoupper($matches[1]);
        }, $className);
        $className = ucfirst($className);
        $className = '\\' . __NAMESPACE__ . '\\Models\\' . $className;

        return forward_static_call_array([$className, 'getInstance'], Auth::get());
    }
}
