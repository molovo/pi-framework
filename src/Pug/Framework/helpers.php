<?php

use Pug\Framework\View;

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
