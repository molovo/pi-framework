<?php

namespace Pug\Http;

use Molovo\Traffic\Route;
use Molovo\Traffic\Router;
use Pug\Framework\Application;

class Redirect
{
    /**
     * Redirect to a URL.
     *
     * @param string $url The URL to redirect to
     */
    public static function to($url)
    {
        $app = Application::instance();
        $app->response->redirect($url);
    }

    /**
     * Redirect to a route.
     *
     * @param Route|string $route The route object or route name
     * @param array        $vars  An array of variables to pass to the route
     */
    public static function route($route, array $vars = [])
    {
        $app = Application::instance();

        if (!($route instanceof Route)) {
            $route = Router::route($route);
        }

        if ($route !== null) {
            $app->response->redirect($route->uri($vars));
        }
    }

    /**
     * Redirect back to the previous URL.
     */
    public static function back()
    {
        $app = Application::instance();
        $app->response->redirect(Url::previous());
    }

    /**
     * Refresh the current page.
     */
    public static function refresh()
    {
        $app = Application::instance();
        $app->response->redirect(Url::current());
    }
}
