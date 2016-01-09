<?php

namespace Pi\Framework\Http;

use Pi\Framework\Http\Response\Codes;

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
    }

    /**
     * Render the output to the screen.
     *
     * @param string $output The output to render
     */
    public function render($output)
    {
        echo $output;

        while (ob_get_level() > 0) {
            echo ob_get_clean();
        }

        exit;
    }
}
