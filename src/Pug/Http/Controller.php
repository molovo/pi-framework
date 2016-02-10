<?php

namespace Pug\Http;

use Pug\Framework\Application;
use Pug\Http\Exceptions\InvalidMiddlewareException;
use Pug\Http\Interfaces\Middleware;
use Pug\Http\Middleware\Csrf;

class Controller
{
    /**
     * The current request.
     *
     * @var Request|null
     */
    protected $request = null;

    /**
     * The current response.
     *
     * @var Response|null
     */
    protected $response = null;

    /**
     * An array of middleware classes.
     *
     * @var Middleware[]
     */
    protected $middleware = [
        Csrf::class,
    ];

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

        foreach ($this->middleware as $middleware) {
            if (!((new $middleware) instanceof Middleware)) {
                throw new InvalidMiddlewareException($middleware.' is not a valid middleware class');
            }

            if (!$middleware::check()) {
                // Most middleware classes will throw an exception
                Application::instance()->error(404);
            }
        }
    }
}
