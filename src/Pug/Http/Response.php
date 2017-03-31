<?php

namespace Pug\Http;

use Pug\Framework\Application;
use Pug\Http\Exceptions\InvalidResponseCodeException;
use Pug\Http\Response\Codes;

class Response
{
    /**
     * The application instance.
     *
     * @var Application
     */
    private $app;

    /**
     * The HTTP response code.
     *
     * @var int
     */
    private $code = 200;

    /**
     * The response headers.
     *
     * @var array
     */
    private $headers = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->setHeader('Accept', 'application/json');
        $this->setHeader('Content-Type', 'text/html');
        $this->setHeader('Origin', Url::full(Url::current()));
        $this->setHeader('Access-Control-Allow-Origin', $app->config->allow_origin);
    }

    /**
     * Set the HTTP response code.
     *
     * @param int $code The code to set
     */
    public function setResponseCode($code = 200)
    {
        if (!Codes::allowed($code)) {
            throw new InvalidResponseCodeException();
        }

        $this->code = $code;

        return $this;
    }

    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }

    public function sendHeader($key, $value)
    {
        $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $key))));

        return header("$key: $value");
    }

    public function json(array $data)
    {
        $this->setHeader('Content-Type', 'application/json');

        return $this->render(json_encode($data));
    }

    /**
     * Render the output to the screen.
     *
     * @param string $output The output to render
     */
    public function render($output)
    {
        // Set the HTTP response code
        http_response_code($this->code);

        foreach ($this->headers as $key => $value) {
            $this->sendHeader($key, $value);
        }

        // Empty all output buffers to the screen
        while (ob_get_level() > 0) {
            echo ob_get_clean();
        }

        // Echo the output passed to the function
        echo $output;
        exit;
    }

    /**
     * Clear output buffers and set redirect headers.
     *
     * @param string $url  The URL to redirect to
     * @param mixed  $code
     */
    public function redirect($url, $code = 301)
    {
        if (!in_array($code, range(301, 308))) {
            throw new InvalidResponseCodeException('Redirect response code must be 301-308');
        }

        // Ensure all output buffers are emptied before redirecting
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Set the HTTP response code
        $this->setResponseCode($code);
        http_response_code($this->code);

        // Set the location header so the redirect is performed
        $this->sendHeader('location', $url);
        exit;
    }
}
