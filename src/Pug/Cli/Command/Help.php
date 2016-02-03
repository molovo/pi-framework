<?php

namespace Pug\Cli\Command;

use Pug\Cli\Application;
use Pug\Cli\Interfaces\Command;

class Help implements Command
{
    /**
     * The base path for templates.
     *
     * @var string
     */
    private static $base = null;

    /**
     * The path to help templates.
     *
     * @var
     */
    private $template = null;

    /**
     * Execute the command.
     *
     * @param Application $app The application instance
     *
     * @return mixed
     */
    public static function execute(Application $app)
    {
        static::$base = dirname(__DIR__).'/templates/help/';
        $help         = new static($app->command);
        $help->render();
    }

    /**
     * Create a new help instance.
     *
     * @param string $command The command being run
     */
    public function __construct($command)
    {
        $this->command  = $command;
        $this->template = static::$base.$this->command.'.php';
    }

    /**
     * Render the help content.
     */
    public function render()
    {
        if (!file_exists($this->template)) {
            throw new FileNotFoundException('The help template for '.$this->command.' could not be found.');
        }
        $help = include $this->template;
        echo $help;
        exit;
    }
}
