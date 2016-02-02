<?php

use Pug\Framework\Application;
use Pug\Framework\View;

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

if (!function_exists('view')) {
    /**
     * Compile a view and return the rendered output.
     *
     * @param string $name The name of the view (relative to app/views)
     * @param array  $vars Variables to pass to the view
     *
     * @return string The compiled HTML
     */
    function view($name, array $vars = [])
    {
        $view = new View($name, $vars);

        return $view->render();
    }
}

if (!function_exists('e')) {
    /**
     * Escape HTML entities in a string.
     *
     * @param string $value The value to escape
     *
     * @return string The escaped value
     */
    function e($value)
    {
        return htmlentities($value, ENT_QUOTES, 'UTF-8', false);
    }
}
