<?php

namespace Pug\Framework;

use Closure;
use Molovo\Amnesia\Cache;
use Molovo\Interrogate\Database;
use Molovo\Traffic\Route;
use Molovo\Traffic\Router;
use Pug\Framework\Exceptions\ConfigNotFoundException;
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
     * Environments which this application matches.
     *
     * @var string[]
     */
    public $environments = [];

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

         return new static();
     }

    /**
     * Bootstrap the application.
     */
    public static function bootstrap()
    {
        return new static();
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

        // Register the error handler
        $this->registerErrorHandler();

        // Create our request and response objects
        $this->request  = new Request($this);
        $this->response = new Response($this);

        // Bootstrap the database
        Database::bootstrap($this->config->db->toArray());

        // Convert relative store paths to absolute, and bootstrap the cache
        foreach ($this->config->cache as $instance => $config) {
            $cacheStorePath = $config->store_path;
            if ($cacheStorePath !== null) {
                if (!is_dir($cacheStorePath) && is_dir(APP_ROOT.$cacheStorePath)) {
                    $this->config->cache->{$instance}->store_path = APP_ROOT.$cacheStorePath;
                }
            }
        }
        Cache::bootstrap($this->config->cache->toArray());

        // Convert relative store paths to absolute, and bootstrap the session
        $sessionStorePath = $this->config->session->store_path;
        if ($sessionStorePath !== null) {
            if (!is_dir($sessionStorePath) && is_dir(APP_ROOT.$sessionStorePath)) {
                $this->config->session->store_path = APP_ROOT.$sessionStorePath;
            }
        }
        Session::bootstrap($this->config->session, $this);

        // Include the app routes
        require APP_ROOT.'routes.php';

        // Register global view variables
        View::addGlobal('appName', $this->config->app->name);
        View::addGlobal('app', $this);
        View::addGlobal('input', $this->request->input);

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
        $base     = APP_ROOT.'config/';
        $filename = $base.'app.yml';
        if (!file_exists($filename)) {
            throw new ConfigNotFoundException('The application config file '.$filename.' could not be found');
        }

        $config = Yaml::parseFile($filename);

        // Populate the environments array
        $hostname     = gethostname();
        $environments = Yaml::parseFile($base.'env.yml');
        foreach ($environments as $env => $hosts) {
            foreach ($hosts as $host) {
                if (fnmatch($host, $hostname)) {
                    $this->environments[] = $env;

                    // Merge the app config for the environment
                    $filename = $base.$env.'/app.yml';
                    if (file_exists($filename)) {
                        $envConfig = Yaml::parseFile($filename);
                        $config    = array_merge($config, $envConfig);
                    }
                }
            }
        }

        // Loop through each of the config files and add them
        // to the main config array
        foreach (glob(APP_ROOT.'config/*.yml') as $file) {
            $key          = str_replace('.yml', '', basename($file));
            $config[$key] = Yaml::parseFile($file);

            // Loop through each of the environments and merge their config
            foreach ($this->environments as $env) {
                $filename = $base.$env.'/'.$key.'.yml';
                if (file_exists($filename)) {
                    $envConfig    = Yaml::parseFile($filename);
                    $config[$key] = array_merge($config[$key], $envConfig);
                }
            }
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
            $run     = new Whoops\Run();
            $handler = new PrettyPageHandler();

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

        return Router::$verb($route, function () use ($app, $callback, $data, $controller) {
            // Merge the arguments returned from the router
            $data = array_merge($data, func_get_args());

            // Execute the callback, and output the result in the response
            if ($controller !== null) {
                $controller = new $controller($app->request, $app->response);

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
