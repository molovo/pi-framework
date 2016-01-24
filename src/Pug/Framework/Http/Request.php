<?php

namespace Pug\Framework\Http;

use Molovo\Traffic\Router;
use Pug\Framework\Http\Request\Input;

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
     * Create a new request for the application.
     */
    public function __construct()
    {
        $this->router = new Router;
        $this->method = $this->router->requestMethod();

        $input           = array_merge($_GET, $_POST);
        $this->rawInput  = new Input($input);

        $input        = $this->escapeInput($input);
        $this->input  = new Input($input);
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
}
