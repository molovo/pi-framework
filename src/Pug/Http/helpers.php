<?php

use Molovo\Str\Str;
use Pug\Framework\Application;

if (!function_exists('get')) {
    /**
     * Bind a route to GET requests.
     *
     * @param string                 $route    The route to register
     * @param string|Closure         $callback The callback to run when
     *                                         route is matched
     * @param bool|array|Traversable $compile  Data to compile pages with
     * @param array|Closure          $vars     Values to populate URI
     *                                         placeholders with
     */
    function get($route, $callback, $compile = null, $vars = [])
    {
        $application = Application::instance();
        $application->registerRoute('get', $route, $callback, $compile, $vars);
    }
}

if (!function_exists('post')) {
    /**
     * Bind a route to POST requests.
     *
     * @param string                 $route    The route to register
     * @param string|Closure         $callback The callback to run when
     *                                         route is matched
     * @param bool|array|Traversable $compile  Data to compile pages with
     * @param array|Closure          $vars     Values to populate URI
     *                                         placeholders with
     */
    function post($route, $callback, $compile = null, $vars = [])
    {
        $application = Application::instance();
        $application->registerRoute('post', $route, $callback, $compile, $vars);
    }
}

if (!function_exists('put')) {
    /**
     * Bind a route to PUT requests.
     *
     * @param string                 $route    The route to register
     * @param string|Closure         $callback The callback to run when
     *                                         route is matched
     * @param bool|array|Traversable $compile  Data to compile pages with
     * @param array|Closure          $vars     Values to populate URI
     *                                         placeholders with
     */
    function put($route, $callback, $compile = null, $vars = [])
    {
        $application = Application::instance();
        $application->registerRoute('put', $route, $callback, $compile, $vars);
    }
}

if (!function_exists('patch')) {
    /**
     * Bind a route to PATCH requests.
     *
     * @param string                 $route    The route to register
     * @param string|Closure         $callback The callback to run when
     *                                         route is matched
     * @param bool|array|Traversable $compile  Data to compile pages with
     * @param array|Closure          $vars     Values to populate URI
     *                                         placeholders with
     */
    function patch($route, $callback, $compile = null, $vars = [])
    {
        $application = Application::instance();
        $application->registerRoute('patch', $route, $callback, $compile, $vars);
    }
}

if (!function_exists('head')) {
    /**
     * Bind a route to HEAD requests.
     *
     * @param string                 $route    The route to register
     * @param string|Closure         $callback The callback to run when
     *                                         route is matched
     * @param bool|array|Traversable $compile  Data to compile pages with
     * @param array|Closure          $vars     Values to populate URI
     *                                         placeholders with
     */
    function head($route, $callback, $compile = null, $vars = [])
    {
        $application = Application::instance();
        $application->registerRoute('head', $route, $callback, $compile, $vars);
    }
}

if (!function_exists('delete')) {
    /**
     * Bind a route to DELETE requests.
     *
     * @param string                 $route    The route to register
     * @param string|Closure         $callback The callback to run when
     *                                         route is matched
     * @param bool|array|Traversable $compile  Data to compile pages with
     * @param array|Closure          $vars     Values to populate URI
     *                                         placeholders with
     */
    function delete($route, $callback, $compile = null, $vars = [])
    {
        $application = Application::instance();
        $application->registerRoute('delete', $route, $callback, $compile, $vars);
    }
}

if (!function_exists('options')) {
    /**
     * Bind a route to OPTIONS requests.
     *
     * @param string                 $route    The route to register
     * @param string|Closure         $callback The callback to run when
     *                                         route is matched
     * @param bool|array|Traversable $compile  Data to compile pages with
     * @param array|Closure          $vars     Values to populate URI
     *                                         placeholders with
     */
    function options($route, $callback, $compile = null, $vars = [])
    {
        $application = Application::instance();
        $application->registerRoute('options', $route, $callback, $compile, $vars);
    }
}

if (!function_exists('any')) {
    /**
     * Bind a route to all HTTP requests.
     *
     * @param string                 $route    The route to register
     * @param string|Closure         $callback The callback to run when
     *                                         route is matched
     * @param bool|array|Traversable $compile  Data to compile pages with
     * @param array|Closure          $vars     Values to populate URI
     *                                         placeholders with
     */
    function any($route, $callback, $compile = null, $vars = [])
    {
        $application = Application::instance();
        $application->registerRoute('any', $route, $callback, $compile, $vars);
    }
}

if (!function_exists('resource')) {
    function resource($route, $controller)
    {
        $application = Application::instance();

        if (!is_array($route)) {
            $route = [
                $route,
                Str::snakeCase(str_replace('Controller', '', $controller)),
            ];
        }

        list($route, $name) = $route;

        get([$route, $name], $controller.'@index');
        post([$route, $name.'.create'], $controller.'@create');
        get([$route.'/{id:int}', $name.'.show'], $controller.'@show');
        post([$route.'/{id:int}', $name.'.update'], $controller.'@update');
        get([$route.'/{id:int}/edit', $name.'.edit'], $controller.'@edit');
        delete([$route.'/{id:int}', $name.'.delete'], $controller.'@destroy');
        post([$route.'/{id:int}/destroy', $name.'.destroy'], $controller.'@destroy');
    }
}
