<?php

namespace Pug\Framework;

use Pug\Framework\Session\Handler;

class Session
{
    public static $handler = null;

    public static function bootstrap(Config $config)
    {
        static::$handler = new Handler($config);
        session_set_save_handler(static::$handler, true);
        session_start();
    }

    public static function get($key)
    {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }

        return;
    }

    public static function set($key, $value)
    {
        return $_SESSION[$key] = $value;
    }
}
