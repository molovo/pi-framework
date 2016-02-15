<?php

namespace Pug\Http;

use Pug\Framework\Application;

class Url
{
    /**
     * Get the current URL.
     *
     * @return string|null The URL
     */
    public static function current()
    {
        $app = Application::instance();

        return $app->request->uri;
    }

    /**
     * Get the previous URL.
     *
     * @return string|null The URL
     */
    public static function previous()
    {
        $app = Application::instance();

        return $app->request->previousUri;
    }

    /**
     * Convert an internal URI into a full external URL.
     *
     * @param string      $uri    The uri
     * @param string|null $domain The domain to build the URL for
     * @param bool        $https  Whether the link should be on https
     *
     * @return string The full URL
     */
    public static function full($uri, $domain = null, $https = false)
    {
        if (!$domain) {
            $domain = $app->config->app->domain;
        }

        $protocol = $app->request->protocol;
        if ($https) {
            $protocol = 'https';
        }

        return $protocol.'://'.$domain.$uri;
    }
}
