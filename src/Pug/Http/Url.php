<?php

namespace Pug\Http;

class Url
{
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
