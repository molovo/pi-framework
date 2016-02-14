<?php

namespace Pug\Compiler;

use Closure;
use Molovo\Traffic\Router;
use Pug\Framework\Application;
use Pug\Http\Exceptions\InvalidControllerException;
use Pug\Http\Exceptions\InvalidControllerMethodException;
use ReflectionClass;
use ReflectionFunction;
use Traversable;
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
     * @param string                 $method   The method name
     * @param string                 $route    The route to match
     * @param Closure                $callback The callback to run on success
     * @param bool|array|Traversable $compile  An iteratable dataset to compile
     * @param Closure                $vars     A callback which provides vars to
     *                                         be injected into the route
     *
     * @return Route
     */
    public function registerRoute($method, $route, $callback, $compile = null, $vars = [])
    {
        $app = $this;

        $controller = null;
        $method     = null;

        $base_uri = $app->config->app->base_uri;

        if (is_array($route)) {
            list($route, $name) = $route;
        }

        $route = $base_uri.$route;

        if (isset($name)) {
            $route = [$route, $name];
        }

        $data = [];

        // If a closure is passed, execute it directly
        if ($callback instanceof Closure) {
            // When we're using a closure, the request and response are returned
            // with the arguments
            $data = [
                $app->request,
                $app->response,
            ];

            // Wrap a ReflectionFunction around the controller so we can use
            // invokeArgs() later
            $callback = new ReflectionFunction($callback);
        }

        // If a string is passed, then it points to a controller
        if (is_string($callback)) {
            // Get the controller and method name
            list($controller, $method) = explode('@', $callback);

            // If the naked class doesn't exist, prepend it with the namespace
            if (!class_exists($controller)) {
                $controller = APP_NAMESPACE.'Http\\Controllers\\'.$controller;
            }

            // If the class still doesn't exist, throw an exception
            if (!class_exists($controller)) {
                throw new InvalidControllerException('The controller '.$controller.' does not exist.');
            }

            // Create a reflection class for the controller
            $ref = new ReflectionClass($controller);

            // If the controller does not have the requested method,
            // throw an exception
            if (!$ref->hasMethod($method)) {
                throw new InvalidControllerMethodException('The method '.$controller.'::'.$method.' does not exist.');
            }

            // Get the callback method
            $callback = $ref->getMethod($method);
        }

        $compiledRoute = Router::$verb($route, function () use ($app, $callback, $data, $controller) {
            // Merge the arguments returned from the router
            $data = array_merge($data, func_get_args());

            // Execute the callback, and output the result in the response
            if ($controller !== null) {
                $controller = new $controller($app->request, $app->response);

                $output = $app->respond($callback->invokeArgs($controller, $data));
            } else {
                $output = $app->respond($callback->invokeArgs($data));
            }

            ob_start();
            echo $output;

            while (ob_get_level() > 1) {
                echo ob_get_clean();
            }

            return ob_get_clean();
        });

        return $this->compile($compiledRoute, $compile, $vars);
        // $app = $this;
        //
        // // We only really want to compile get requests
        // if ($method !== Router::GET || !$compile) {
        //     return;
        // }
        //
        // $base_uri = $app->config->app->base_uri;
        //
        // if (is_array($route)) {
        //     list($route, $name) = $route;
        // }
        //
        // $route = $base_uri.$route;
        //
        // if (isset($name)) {
        //     $route = [$route, $name];
        // }
        //
        // // Compile the route as if we were on the front end, but use a different
        // // callback which returns the output from the callback, rather than
        // // echoing it to the output buffer
        // $compiledRoute = Router::$method($route, function () use ($app, $callback) {
        //     $output = null;
        //
        //     // If a closure is passed, execute it directly
        //     if ($callback instanceof Closure) {
        //         // Add the request and response objects to the arguments
        //         $data = [
        //             $app->request,
        //             $app->response,
        //         ];
        //         $args = array_merge($data, func_get_args());
        //
        //         // Output the results of the callback
        //         $output = call_user_func_array($callback, $args);
        //     }
        //
        //     if (is_string($callback)) {
        //         // Get the controller and method name
        //         list($class, $method) = explode('@', $callback);
        //
        //         // If the naked class doesn't exist, prepend it with the namespace
        //         if (!class_exists($class, false)) {
        //             $class = APP_NAMESPACE.'Controllers\\'.$class;
        //         }
        //
        //         // If the class still doesn't exist, throw an exception
        //         if (!class_exists($class)) {
        //             throw new InvalidControllerException('The controller '.$class.' does not exist.');
        //         }
        //
        //         // Create a reflection class for the controller
        //         $ref = new ReflectionClass($class);
        //
        //         // If the controller does not have the requested method,
        //         // throw an exception
        //         if (!$ref->hasMethod($method)) {
        //             throw new InvalidControllerMethodException('The method '.$class.'::'.$method.' does not exist.');
        //         }
        //
        //         // Initialise the controller object
        //         $controller = new $class($app->request, $app->response);
        //
        //         // Get the callback method
        //         $method = $ref->getMethod($method);
        //
        //         // Get the arguments returned by the router
        //         $args = func_get_args();
        //
        //         // Output the response from the controller
        //         $output = $method->invokeArgs($controller, $args);
        //     }
        //
        //     ob_start();
        //     echo $output;
        //
        //     while (ob_get_level() > 1) {
        //         echo ob_get_clean();
        //     }
        //
        //     return ob_get_clean();
        // });
        //
        // return $this->compile($compiledRoute, $compile, $vars);
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
     * @param Route         $route   The compiled route
     * @param mixed         $compile Bool, or an iteratable dataset to compile
     * @param array|Closure $vars    An array of vars, or a callback which
     *                               provides vars to be injected into the
     *                               route placeholders
     */
    public function compile($route, $data, $vars)
    {
        if ($data === true) {
            $data = [$data];
        }

        if (!(is_array($data) || $data instanceof Traversable)) {
            return;
        }

        // Loop through the provided data
        foreach ($data as $key => $value) {
            $params = $vars;

            if ($vars instanceof Closure) {
                // Run the compile callback to get the vars to include in the URI
                $params = $vars($value, $key);
            }

            // Pass the vars to the route, and spoof the returned URI
            $uri                    = $route->uri($params);
            $_SERVER['REQUEST_URI'] = $uri;
            $this->request->uri     = $uri;

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
        if (strpos($uri, '/') !== 0) {
            $uri = '/'.$uri;
        }

        // Check if the directory exists, and if not create it
        $base = PUB_ROOT.'static';
        $dir  = $base.$uri;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Get the filename and remove any double slashes,
        // just in case
        $filename = $dir.DS.'index.html';
        $filename = preg_replace('#[\/]+#', '/', $filename);

        $file = fopen($filename, 'w');
        fwrite($file, $result);
        fclose($file);
    }
}
