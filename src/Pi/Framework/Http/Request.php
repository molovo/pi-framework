<?php

namespace Pi\Framework\Http;

use Molovo\Traffic\Router;
use Pi\Framework\Http\Request\Input;

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
        $this->input  = new Input(array_merge($_GET, $_POST));
    }

    /**
     * Get an input var, or return all if no key is passed.
     *
     * @param string|null $key The key to fetch
     *
     * @return mixed The value
     */
    public function input($key = null)
    {
        if ($key === null) {
            return $this->input;
        }

        return isset($this->input[$key]) ? $this->input[$key] : null;
    }
}
