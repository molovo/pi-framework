<?php

namespace Pug\Framework\Http;

use Pug\Framework\Http\Response\Codes;

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

    /**
     * Render the output to the screen.
     *
     * @param string $output The output to render
     */
    public function render($output)
    {
        http_response_code($this->code);

        echo $output;

        while (ob_get_level() > 0) {
            echo ob_get_clean();
        }

        exit;
    }
}
