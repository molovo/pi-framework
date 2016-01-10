<?php

namespace Pi\Framework;

use Molovo\Object\Object;
use Pi\Framework\Application;

class Config extends Object
{
    /**
     * Get a config value.
     *
     * @param string $path The path of the value to get
     *
     * @return mixed The value
     */
    public static function get($key)
    {
        return Application::instance()->config->valueForPath($key);
    }

    /**
     * Set a config value.
     *
     * @param string $path The path of the value to set
     *
     * @return mixed The value
     */
    public static function set($key)
    {
        return Application::instance()->config->setValueForPath($key);
    }
}
