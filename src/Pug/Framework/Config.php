<?php

namespace Pug\Framework;

use Molovo\Object\Object;

class Config extends Object
{
    /**
     * Get a config value.
     *
     * @param string $path The path of the value to get
     * @param string $key
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
     * @param string $key
     *
     * @return mixed The value
     */
    public static function set($key)
    {
        return Application::instance()->config->setValueForPath($key);
    }
}
