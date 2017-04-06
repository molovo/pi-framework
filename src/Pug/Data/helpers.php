<?php

use Pug\Data\DataLoader;

if (!function_exists('data')) {
    /**
     * Load a dataset from a file.
     *
     * @param string       $filename The filename (relative to app/data)
     * @param Closure|null $modifier A modifier closure to apply to each
     *                               row of the dataset
     *
     * @return array|null
     */
    function data($filename, Closure $modifier = null)
    {
        return DataLoader::loadFromFile($filename, $modifier);
    }
}
