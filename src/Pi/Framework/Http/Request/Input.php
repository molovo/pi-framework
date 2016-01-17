<?php

namespace Pi\Framework\Http\Request;

use Molovo\Object\Object;
use Pi\Framework\Application;

class Input extends Object
{
    /**
     * Grab all get and post variables, and create a new object with them.
     */
    public function __construct()
    {
        $values = array_merge($_GET, $_POST);
        parent::__construct($values);
    }

    /**
     * Get a value from the input array. If multiple arguments or an array are
     * passed, then an array of values are returned.
     *
     * @param string|array $key The key (or keys) to fetch
     *
     * @return mixed
     */
    public static function get($key)
    {
        if (is_string($key) && func_num_args() === 1) {
            return Application::instance()->input->valueForKey($key);
        }

        $keys = is_array($key) ? $key : func_get_args();

        $values = [];
        foreach ($keys as $key) {
            $values[$key] = Application::instance()->input->valueForKey($key);
        }

        return $values;
    }

    /**
     * Get all the input arguments from the array.
     *
     * @return array
     */
    public static function all()
    {
        return Application::instance()->request->input->toArray();
    }
}
