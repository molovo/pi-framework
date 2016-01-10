<?php

namespace Pi\Framework;

use Closure;
use Molovo\Interrogate\Database;
use Molovo\Traffic\Route;
use Molovo\Traffic\Router;
use Pi\Framework\Config;
use Pi\Framework\Http\Request;
use Pi\Framework\Http\Response;
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

        // Register the error handler
        $this->registerErrorHandler();

        // Create our request and response objects
        $this->request  = new Request;
        $this->response = new Response;

        // Include the app routes
        require APP_ROOT.'routes.php';

        // Load the app's config
        $this->loadConfig();

        // Bootstrap the database
        Database::bootstrap($this->config->db->toArray());

        // Register global view variables
        View::addGlobal('appName', $this->config->app->name);

        // Execute routes
        Router::execute();
    }

    /**
     * Set the HTTP response code and render the response.
     *
     * @param string $output The output to render
     * @param int    $code   The HTTP response code
     */
    public function response($output, $code = 200)
    {
        $this->response->setResponseCode($code);
        $this->response->render($output);
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
        $run     = new Whoops\Run;
        $handler = new PrettyPageHandler;

        $run->pushHandler($handler);
        $run->register();
    }

    /**
     * Register a route for the application.
     *
     * @param string  $method   The method name
     * @param string  $route    The route to match
     * @param Closure $callback The callback to run on success
     *
     * @return Route
     */
    public function registerRoute($method, $route, Closure $callback)
    {
        $app = $this;

        return Router::$method($route, function () use ($app, $callback) {
            $data = [
                $app->request,
                $app->response,
            ];
            $args = array_merge($data, func_get_args());
            $app->response->render(call_user_func_array($callback, $args));
        });
    }
}
