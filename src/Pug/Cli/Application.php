<?php

namespace Pug\Cli;

use Molovo\Str\Str;
use Pug\Cli\Command\Help;
use Pug\Cli\Prompt\ANSI;
use Pug\Framework\Config;
use Whoops;
use Whoops\Handler\PlainTextHandler;

class Application
{
    /**
     * The current application instance.
     *
     * @var self|null
     */
    protected static $instance = null;

    /**
     * Environment variables.
     *
     * @var array
     */
    public $env = null;

    /**
     * The command line arguments.
     *
     * @var string[]
     */
    public $args = null;

    /**
     * The current command.
     *
     * @var string|null
     */
    public $command = null;

    /**
     * Create a new CLI application.
     */
    public function __construct()
    {
        // Load the app's config
        $this->loadConfig();

        $this->registerErrorHandler();

        // Get the arguments array
        if (!isset($argv) || $argv === null) {
            $argv = $_SERVER['argv'];
        }

        // Remove the application name as we don't need it
        array_shift($argv);

        // Remove the command from the argument list and store it separately
        $this->command = array_shift($argv);

        // Save the arguments
        $this->args = $argv;

        // Save environment variables
        $this->env = $_ENV;
    }

    /**
     * Bootstrap the application.
     */
    public static function bootstrap()
    {
        static::$instance = new static;
        static::$instance->executeCommand();
    }

    /**
     * Get the current application instance.
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
     * Execute the command.
     */
    public function executeCommand()
    {
        $command = $this->command;

        $class = 'Pug\\Cli\\Command\\'.Str::camelCaps($command);

        if (!class_exists($class)) {
            Prompt::outputend(ANSI::fg('The command "'.$command.'" could not be found.'."\n", ANSI::RED));
            $this->command = 'help';
            Help::execute($this);
        }

        $class::execute($this);
    }
}
