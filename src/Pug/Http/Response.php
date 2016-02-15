<?php

namespace Pug\Http;

use Molovo\Str\Str;
use Pug\Http\Response\Codes;

class Response
{
    /**
     * The HTTP response code.
     *
     * @var int
     */
    private $code = 200;

    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * Set the HTTP response code.
     *
     * @param int $code The code to set
     */
    public function setResponseCode($code = 200)
    {
        if (!Codes::allowed($code)) {
            throw new InvalidResponseCodeException;
        }

        $this->code = $code;

        return $this;
    }

    public function setHeader($key, $value)
    {
        $key = Str::camelCaps($key);

        return header("$key: $value");
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
     * @param string $url The URL to redirect to
     */
    public function redirect($url)
    {
        // Ensure all output buffers are emptied before redirecting
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Set the HTTP response code
        $this->setResponseCode(301);
        http_response_code($this->code);

        // Set the location header so the redirect is performed
        $this->setHeader('location', $url);
        exit;
    }
}
