<?php

namespace Pug\Compiler;

use Closure;
use Molovo\Traffic\Router;
use Pug\Framework\Application;
use Whoops;
use Whoops\Handler\PlainTextHandler;

class PageCompiler extends Application
{
    /**
     * Construct the application.
     */
    public function __construct()
    {
        // Spoof the request method
        $_SERVER['REQUEST_METHOD'] = Router::GET;

        parent::__construct();
    }

    /**
     * Overrides the default application registerRoute method, spoofing the URL
     * to match the route, executing it, and then writing the response to a file
     * matching the spoofed URL.
     *
     * @param string  $method          The method name
     * @param string  $route           The route to match
     * @param Closure $callback        The callback to run on success
     * @param mixed   $compileData     An iteratable dataset to compile for
     * @param Closure $compileCallback A callback which provides vars to be
     *                                 injected into the route placeholders
     *
     * @return Route
     */
    public function registerRoute($method, $route, Closure $callback, $compileData = null, Closure $compileCallback = null)
    {
        $app = $this;

        // We only really want to compile get requests
        if ($method !== Router::GET || !$compileData) {
            return;
        }

        // Compile the route as if we were on the front end, but use a different
        // callback which returns the output from the callback, rather than
        // echoing it to the output buffer
        $compiledRoute = Router::$method($route, function () use ($app, $callback) {
            $data = [
                $app->request,
                $app->response,
            ];
            $args = array_merge($data, func_get_args());

            ob_start();
            echo call_user_func_array($callback, $args);

            while (ob_get_level() > 1) {
                echo ob_get_clean();
            }

            return ob_get_clean();
        });

        return $this->compile($compiledRoute, $compileData, $compileCallback);
    }

    /**
     * The main application checks that a valid route is found.
     * Since we're not properly running the application a route,
     * we override this method to do nothing, so that the 404
     * page is not returned.
     */
    protected function checkRoute()
    {
    }

    /**
     * Register the error handler for the application.
     */
    private function registerErrorHandler()
    {
        $run     = new Whoops\Run;
        $handler = new PlainTextHandler;

        $run->pushHandler($handler);
        $run->register();
    }

    /**
     * Execute a compiled route, and store the output to a file.
     *
     * @param Route   $route    The compiled route
     * @param mixed   $data     An iteratable dataset to compile for
     * @param Closure $callback A callback which provides vars to be
     *                          injected into the route placeholders
     */
    public function compile($route, $data, $callback)
    {
        // Loop through the provided data
        foreach ($data as $key => $value) {
            // Run the compile callback to get the vars to include in the URI
            $vars = $callback($value, $key);

            // Pass the vars to the route, and spoof the returned URI
            $uri                    = $route->uri($vars);
            $_SERVER['REQUEST_URI'] = $uri;

            // Execute the route, which now matches, and capture the results
            $result = $route->execute();

            $this->store($uri, $result);
        }
    }

    /**
     * Store compiled HTML to a file.
     *
     * @param string $uri    The uri to write to
     * @param string $result The compiled HTML
     */
    private function store($uri, $result)
    {
        // Remove the leading slash from the uri
        if (strpos($uri, '/') === 0) {
            $uri = substr($uri, 1);
        }

        // Delete and recreate the static directory
        $base = PUB_ROOT.'static';
        if (is_dir($base)) {
            rmdir($base);
            mkdir($base, 0755);
        }

        // Check if the directory exists, and if not create it
        if (!is_dir($base.DS.$uri)) {
            mkdir($base.DS.$uri, 0755, true);
        }

        // Write the compiled view to the file
        $file = fopen($base.DS.$uri.DS.'index.html', 'w');
        fwrite($file, $result);
        fclose($file);
    }
}
