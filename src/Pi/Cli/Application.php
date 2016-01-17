<?php

namespace Pi\Cli;

use Molovo\Str\Str;
use Pi\Cli\Exceptions\CommandNotFoundException;

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

        // Execute the command
        $this->executeCommand();
    }

    /**
     * Bootstrap the application.
     */
    public static function bootstrap()
    {
        static::$instance = new static;
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
     * Execute the command.
     */
    private function executeCommand()
    {
        $command = $this->command;

        $class = 'Pi\\Cli\\Command\\'.Str::camelCaps($command);

        if (!class_exists($class)) {
            throw new CommandNotFoundException('The command '.$command.' could not be found.');
        }

        $class::execute($this);
    }
}
