<?php

namespace Pug\Http;

use Molovo\Traffic\Router;
use Pug\Framework\Application;
use Pug\Framework\Session;
use Pug\Http\Request\Input;

class Request
{
    /**
     * The application instance.
     *
     * @var Application
     */
    private $app;

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
     * The previous URI.
     *
     * @var string|null
     */
    public $previousUri = null;

    /**
     * The request headers.
     *
     * @var string[]
     */
    private $headers = [];

    /**
     * Create a new request for the application.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app    = $app;
        $this->router = new Router();
        $this->method = strtoupper($this->router->requestMethod());

        $this->parseHeaders();

        // Store the raw input data
        $input          = $this->getInput();
        $this->rawInput = new Input($input);

        // Escape the input data, and store it again
        $input       = $this->escapeInput($input);
        $this->input = new Input($input);

        // Store the current URI
        if (isset($_SERVER['REQUEST_URI'])) {
            $this->uri = $_SERVER['REQUEST_URI'];
        }

        if (Session::isStarted()) {
            // Retrieve the previous URI from the session, and store it
            // against the request object
            if (($previous = Session::get('previous_uri')) !== null) {
                $this->previousUri = $previous;
            }

            // Update the previous URI session key now that we have retrieved
            // it's value
            Session::set('previous_uri', $this->uri);
        }
    }

    /**
     * Get input data from the request.
     *
     * @return array
     */
    private function getInput()
    {
        if ($this->contentType('application/json')) {
            return json_decode(file_get_contents('php://input'), true);
        }

        return array_merge($_GET, $_POST);
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
        foreach ($input as &$value) {
            if (is_array($value)) {
                $value = $this->escapeInput($value);
                continue;
            }

            $value = e($value);
        }

        return $input;
    }

    private function parseHeaders()
    {
        $headers = [];

        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $name           = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$name] = $value;
            } elseif ($name == 'CONTENT_TYPE') {
                $headers['Content-Type'] = $value;
            } elseif ($name == 'CONTENT_LENGTH') {
                $headers['Content-Length'] = $value;
            }
        }

        return $this->headers = $headers;
    }

    /**
     * Check if the Accept header matches the passed type.
     *
     * @param string $type
     *
     * @return bool
     */
    public function accepts($type)
    {
        return isset($this->headers['Accept']) && $this->headers['Accept'] === $type;
    }

    /**
     * Check if the Content-Type header matches the passed type.
     *
     * @param string $type
     *
     * @return bool
     */
    public function contentType($type)
    {
        return isset($this->headers['Content-Type']) && $this->headers['Content-Type'] === $type;
    }

    /**
     * Tries a few common methods to detmerine if we are on an SSL connection.
     *
     * @return bool
     */
    public function isSecure()
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            return true;
        }

        if (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https') {
            return true;
        }

        if ((!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])
            && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
            || (!empty($_SERVER['HTTP_X_FORWARDED_SSL'])
            && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on')) {
            return true;
        }

        return false;
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
