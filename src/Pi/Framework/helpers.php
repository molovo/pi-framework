<?php

use Pi\Framework\Application;
use Pi\Framework\View;

if (!function_exists('get')) {
    function get($route, Closure $callback, $compileData = null, Closure $compileCallback = null)
    {
        $application = Application::instance();
        $application->registerRoute('get', $route, $callback, $compileData, $compileCallback);
    }
}

if (!function_exists('post')) {
    function post($route, Closure $callback, $compileData = null, Closure $compileCallback = null)
    {
        $application = Application::instance();
        $application->registerRoute('post', $route, $callback, $compileData, $compileCallback);
    }
}

if (!function_exists('put')) {
    function put($route, Closure $callback, $compileData = null, Closure $compileCallback = null)
    {
        $application = Application::instance();
        $application->registerRoute('put', $route, $callback, $compileData, $compileCallback);
    }
}

if (!function_exists('patch')) {
    function patch($route, Closure $callback, $compileData = null, Closure $compileCallback = null)
    {
        $application = Application::instance();
        $application->registerRoute('patch', $route, $callback, $compileData, $compileCallback);
    }
}

if (!function_exists('head')) {
    function head($route, Closure $callback, $compileData = null, Closure $compileCallback = null)
    {
        $application = Application::instance();
        $application->registerRoute('head', $route, $callback, $compileData, $compileCallback);
    }
}

if (!function_exists('delete')) {
    function delete($route, Closure $callback, $compileData = null, Closure $compileCallback = null)
    {
        $application = Application::instance();
        $application->registerRoute('delete', $route, $callback, $compileData, $compileCallback);
    }
}

if (!function_exists('options')) {
    function options($route, Closure $callback, $compileData = null, Closure $compileCallback = null)
    {
        $application = Application::instance();
        $application->registerRoute('options', $route, $callback, $compileData, $compileCallback);
    }
}

if (!function_exists('any')) {
    function any($route, Closure $callback, $compileData = null, Closure $compileCallback = null)
    {
        $application = Application::instance();
        $application->registerRoute('any', $route, $callback, $compileData, $compileCallback);
    }
}

if (!function_exists('view')) {
    function view($name, array $vars = [])
    {
        $view = new View($name, $vars);

        return $view->render();
    }
}
