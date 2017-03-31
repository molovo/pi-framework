<?php

namespace Pug\Framework;

use Molovo\Object\Object;
use Pug\Framework\Session\Handler;
use Pug\Http\Cookie;
use Pug\Http\Request;

class Session
{
    /**
     * Whether the session has been started.
     *
     * @var bool
     */
    private static $isStarted = false;

    /**
     * The store which contains session data.
     *
     * @var object
     */
    private static $store;

    /**
     * The session handler.
     *
     * @var Handler
     */
    public static $handler = null;

    /**
     * Bootstrap the session.
     *
     * @param Config $config The session config
     */
    public static function bootstrap(Config $config)
    {
        if (!$config->cookie_name) {
            $config->cookie_name = 'session_id';
        }

        if (!$config->id_format) {
            $config->id_format = Handler::DEFAULT_ID_FORMAT;
        }

        // Create and set the session save handler
        static::$handler = new Handler($config);
        session_set_save_handler(static::$handler, true);

        // Set the session name
        session_name($config->cookie_name);

        // Set the cookie parameters
        $app      = Application::instance();
        $path     = $app->config->base_uri ?: '/';
        $domain   = $app->config->domain ?: $_SERVER['HTTP_HOST'];
        $secure   = $app->request->isSecure();
        $lifetime = $config->lifetime ?: 2592000;
        session_set_cookie_params($lifetime, $path, $domain, $secure, true);

        // Start the session
        session_start();

        static::$isStarted = true;

        static::$store = $store = new Object($_SESSION);

        // Register session_write_close() as a shutdown function
        $shutdown_handler = function () use ($store) {
            $_SESSION = $store->toArray();
        };
        register_shutdown_function($shutdown_handler);
        session_register_shutdown();
    }

    /**
     * Whether the session has been started.
     *
     * @return bool
     */
    public static function isStarted()
    {
        return static::$isStarted;
    }

    /**
     * Retrieve the session storage object.
     *
     * @return object
     */
    private static function store()
    {
        if (!static::isStarted()) {
            static::bootstrap();
        }

        return static::$store;
    }

    /**
     * Save the session state.
     */
    private static function save()
    {
        $_SESSION = static::store()->toArray();
    }

    /**
     * Check if a value exists in the session.
     *
     * @param string $key The session key
     *
     * @return bool
     */
    public static function has($key)
    {
        return static::store()->valueForPath($key) !== null;
    }

    /**
     * Get a value from the session.
     *
     * TODO: Replace $_SESSION superglobal with Molovo\Object\Object instance
     *
     * @param string     $key     The session key
     * @param null|mixed $default
     *
     * @return mixed The value
     */
    public static function get($key, $default = null)
    {
        if (!static::has($key)) {
            return $default;
        }

        return static::store()->valueForPath($key);
    }

    /**
     * Store a value in the session.
     *
     * TODO: Replace $_SESSION superglobal with Molovo\Object\Object instance
     *
     * @param string $key   The session key
     * @param mixed  $value The value to set
     */
    public static function set($key, $value)
    {
        static::store()->setValueForPath($key, $value);
        static::save();
    }
}
