<?php

namespace Pug\Http;

use Pug\Framework\Application;

class Cookie
{
    public static function get($key)
    {
        if (isset($_COOKIE[$key])) {
            return $_COOKIE[$key];
        }

        return;
    }

    public static function set($key, $value, $expiry = 2592000)
    {
        $app     = Application::instance();
        $path    = $app->config->app->base_uri ?: '/';
        $domain  = $app->config->app->domain ?: $_SERVER['HTTP_HOST'];
        $secure  = $app->request->isSecure();
        $expires = time() + $expiry;
        setcookie($key, $value, $expires, $path, $domain, $secure, true);
    }
}
