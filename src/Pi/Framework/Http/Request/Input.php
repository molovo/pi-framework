<?php

namespace Pi\Framework\Http\Request;

class Input
{
    private $data = [];

    private $rawData = [];

    public function __construct()
    {
        $data = array_merge($_GET, $_POST);

        foreach ($data as $key => $value) {
            $this->rawData[$key] = $value;
            $this->data[$key]    = filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }
    }

    public function __get($key)
    {
        return $this->get($key, true);
    }

    public function get($key, $escaped = true)
    {
        $data = $escaped ? $this->data : $this->rawData;

        if (is_array($key)) {
            $rtn = [];

            foreach ($key as $keyName) {
                $rtn[$keyName] = $data[$key];
            }

            return $rtn;
        }

        return $data[$key];
    }

    public function all($escaped = true)
    {
        return $escaped ? $this->data : $this->rawData;
    }
}
