<?php

namespace Pug\Http\Interfaces;

interface Middleware
{
    /**
     * Check whether the request is valid.
     *
     * @return bool
     */
    public static function check();
}
