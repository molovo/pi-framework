<?php

namespace Pug\Framework;

use Closure;
use Molovo\Amnesia\Cache;
use Molovo\Interrogate\Database;
use Molovo\Traffic\Route;
use Molovo\Traffic\Router;
use Pug\Compiler\Compiler;
use Pug\Http\Exceptions\InvalidControllerException;
use Pug\Http\Exceptions\InvalidControllerMethodException;
use Pug\Http\Request;
use Pug\Http\Response;
use ReflectionClass;
use ReflectionFunction;
use Whoops;
use Whoops\Handler\PrettyPageHandler;

class Application
{
    /**
     * The current application instance.
     *
     * @var self|null
     */
    protected static $instance = null;

    /**
     * The current request object.
     *
     * @var Request|null
     */
    public $request = null;

    /**
     * The current response object.
     *
     * @var Response|null
     */
    public $response = null;

    /**
     * The application config.
     *
     * @var Config|null
     */
    public $config = null;

    /**
     * Retrieve the current application instance.
     *
     * @return self
     */
     public static function instance()
     {
         if (static::$instance !== null) {
             return static::$instance;
         }

         return new static;
     }

    /**
     * Bootstrap the application.
     */
    public static function bootstrap()
    {
        return new static;
    }

    /**
     * Create a new application instance.
     */
    public function __construct()
    {
        // Store the instance statically
        static::$instance = $this;

        // Load the app's config
        $this->loadConfig();

        // Create our request and response objects
        $this->request  = new Request;
        $this->response = new Response;

        // Register the error handler
        $this->registerErrorHandler();

        // Register global view variables
        View::addGlobal('appName', $this->config->app->name);
        View::addGlobal('app', $this);

        // Include the app routes
        require APP_ROOT.'routes.php';

        // Bootstrap the database and cache
        Database::bootstrap($this->config->db->toArray());
        Cache::bootstrap($this->config->cache->toArray());

        $this->compileAssets();

        // Execute routes
        Router::execute();

        if (PHP_SAPI !== 'cli') {
            $this->checkRoute();
        }
    }

    /**
     * Check that a valid route has been found.
     */
    protected function checkRoute()
    {
        if (Router::current() === null) {
            return $this->error(404);
        }
    }

    /**
     * Compile the assets for the application.
     */
    protected function compileAssets()
    {
        if ($this->config->assets->live) {
            $classMap = Compiler::$classMap;

            // We don't want to compile pages here
            unset($classMap['pages']);

            foreach ($classMap as $scope => $scopeClass) {
                $config = $this->config->assets->{$scope};

                if ($config) {
                    $config->clean = $this->config->assets->clean;
                    $compiler      = new $scopeClass($config);
                    $compiler->compile();
                }
            }
        }
    }

    /**
     * Set the HTTP response code and render the response.
     *
     * @param string $output The output to render
     * @param int    $code   The HTTP response code
     */
    public function respond($output, $code = 200)
    {
        $this->response->setResponseCode($code);

        return $this->response->render($output);
    }

    /**
     * Load and store the application config.
     *
     * @return Config
     */
    private function loadConfig()
    {
        $config = [];

        // Loop through each of the config files and add them
        // to the main config array
        foreach (glob(APP_ROOT.'config'.DS.'*.php') as $file) {
            $key          = str_replace('.php', '', basename($file));
            $config[$key] = include $file;
        }

        // Create and store the config object
        return $this->config = new Config($config);
    }

    /**
     * Register the error handler for the application.
     */
    private function registerErrorHandler()
    {
        if ($this->config->app->dev_mode === true) {
            $run     = new Whoops\Run;
            $handler = new PrettyPageHandler;

            $run->pushHandler($handler);

            return $run->register();
        }

        ini_set('display_errors', 'Off');

        $app = $this;
        register_shutdown_function(function () use ($app) {
            if ($error = error_get_last()) {
                $app->error(500);
            }
        });
    }

    /**
     * Register a route for the application.
     *
     * @param string         $verb     The method name
     * @param string         $route    The route to match
     * @param string|Closure $callback The callback to run on success
     * @param mixed          $compile  Boolean for whether the route should be
     *                                 compiled, or a traversible dataset to
     *                                 compile the route for
     * @param mixed          $vars     Variables to substitute into the route
     *                                 when compiling
     *
     * @return Route
     */
    public function registerRoute($verb, $route, $callback, $compile = null, $vars = [])
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
            list($class, $method) = explode('@', $callback);

            // If the naked class doesn't exist, prepend it with the namespace
            if (!class_exists($class)) {
                $class = APP_NAMESPACE.'Controllers\\'.$class;
            }

            // If the class still doesn't exist, throw an exception
            if (!class_exists($class)) {
                throw new InvalidControllerException('The controller '.$class.' does not exist.');
            }

            // Create a reflection class for the controller
            $ref = new ReflectionClass($class);

            // If the controller does not have the requested method,
            // throw an exception
            if (!$ref->hasMethod($method)) {
                throw new InvalidControllerMethodException('The method '.$class.'::'.$method.' does not exist.');
            }

            // Initialise the controller object
            $controller = new $class($app->request, $app->response);

            // Get the callback method
            $callback = $ref->getMethod($method);
        }

        return Router::$verb($route, function () use ($app, $callback, $data, $controller) {
            // Merge the arguments returned from the router
            $data = array_merge($data, func_get_args());

            // Execute the callback, and output the result in the response
            if ($controller !== null) {
                return $app->respond($callback->invokeArgs($controller, $data));
            }

            return $app->respond($callback->invokeArgs($data));
        });
    }

    /**
     * Render an error page.
     *
     * @param int $code The HTTP response code
     *
     * @return string The rendered response
     */
    public function error($code = 500)
    {
        $view = view('errors/'.$code);

        return $this->respond($view, $code);
    }
}
