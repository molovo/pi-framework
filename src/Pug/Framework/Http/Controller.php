<?php

namespace Pug\Framework\Http;

class Controller
{
    /**
     * The current request.
     *
     * @var Request|null
     */
    private $request = null;

    /**
     * The current response.
     *
     * @var Response|null
     */
    private $response = null;

    /**
     * Initialise the controller.
     *
     * @param Request  $request  The current request
     * @param Response $response The current response
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request  = $request;
        $this->response = $response;
    }
}
