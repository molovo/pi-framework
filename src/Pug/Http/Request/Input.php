<?php

namespace Pug\Http\Request;

use Molovo\Object\Object;
use Pug\Framework\Application;

class Input extends Object
{
    /**
     * Get a value from the input array. If multiple arguments or an array are
     * passed, then an array of values are returned.
     *
     * @param string|array $key     The key (or keys) to fetch
     * @param mixed        $escaped
     *
     * @return mixed
     */
    public static function get($key, $escaped = true)
    {
        $request = Application::instance()->request;
        $input   = $escaped ? $request->input : $request->rawInput;

        if (is_string($key) && func_num_args() === 1) {
            return $input->valueForPath($key);
        }

        $keys = is_array($key) ? $key : func_get_args();

        $values = [];
        foreach ($keys as $key) {
            $values[$key] = $input->valueForPath($key);
        }

        return $values;
    }

    /**
     * Get all the input arguments from the array.
     *
     * @param mixed $escaped
     *
     * @return array
     */
    public static function all($escaped = true)
    {
        $request = Application::instance()->request;
        $input   = $escaped ? $request->input : $request->rawInput;

        return $input->toArray();
    }
}
