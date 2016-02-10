<?php

namespace Pug\Http;

use Molovo\Traffic\Router;
use Pug\Http\Request\Input;

class Request
{
    /**
     * Request vars.
     *
     * @var array
     */
    public $input = [];

    /**
     * The current request method.
     *
     * @var string|null
     */
    public $method = null;

    /**
     * The current router object.
     *
     * @var Router|null
     */
    public $router = null;

    /**
     * The current URI.
     *
     * @var string|null
     */
    public $uri = null;

    /**
     * Create a new request for the application.
     */
    public function __construct()
    {
        $this->router = new Router;
        $this->method = strtoupper($this->router->requestMethod());

        $input          = array_merge($_GET, $_POST);
        $this->rawInput = new Input($input);

        $input       = $this->escapeInput($input);
        $this->input = new Input($input);

        if (isset($_SERVER['REQUEST_URI'])) {
            $this->uri = $_SERVER['REQUEST_URI'];
        }
    }

    /**
     * Recursively escape an input array.
     *
     * @param array $input The input array
     *
     * @return array The escaped input
     */
    private function escapeInput(array $input = [])
    {
        foreach ($input as $key => &$value) {
            if (is_array($value)) {
                $value = $this->escapeInput($value);
                continue;
            }

            $value = e($value);
        }

        return $input;
    }

    public function isPost()
    {
        return in_array($this->method, ['POST', 'PUT', 'PATCH', 'DELETE']);
    }

    public function isPut()
    {
        return $this->method === 'PUT' || ($this->method === 'POST' && $this->input->_method === 'PUT');
    }

    public function isPatch()
    {
        return $this->method === 'PATCH' || ($this->method === 'POST' && $this->input->_method === 'PATCH');
    }

    public function isDelete()
    {
        return $this->method === 'DELETE' || ($this->method === 'POST' && $this->input->_method === 'DELETE');
    }
}
