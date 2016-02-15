<?php

namespace Pug\Cli\Command;

use Molovo\Traffic\Router;
use Pug\Cli\Application;
use Pug\Cli\Interfaces\Command as CommandInterface;
use Pug\Cli\Prompt;
use Pug\Cli\Prompt\ANSI;

class Routes implements CommandInterface
{
    public static function execute(Application $app)
    {
        $command = new static($app);
        $scope   = $command->scope;

        if (!method_exists(self::class, $scope) && $scope !== 'help') {
            Prompt::outputend(ANSI::fg('The scope "'.$scope.'" could not be found.'."\n", ANSI::RED));

            Help::execute($app);
            exit(127);
        }

        if ($scope === self::HELP) {
            Help::execute($app);
            exit;
        }

        return $command->executeScope();
    }

    public function __construct(Application $app)
    {
        $this->args  = $app->args;
        $this->scope = isset($this->args[0]) ? array_shift($this->args) : 'all';
    }

    public function executeScope()
    {
        return $this->{$this->scope}();
    }

    public function all()
    {
        $this->loadRoutes();

        Prompt::tableHeader([
            'Verb'  => 10,
            'Route' => 35,
            'Name'  => 20,
            // 'Controller' => 20,
            // 'Method'     => 20,
        ]);

        foreach (Router::routes() as $route) {
            $data = [
                strtoupper($route->verb),
                $route->route,
                $route->name !== $route->route
                    ? $route->name
                    : ' ',
                // $route->controller !== null
                //     ? get_class($route->controller)
                //     : ' ',
                // $route->method !== null
                //     ? $route->method->getPrototype()->name
                //     : ' ',
            ];

            Prompt::tableRow($data);
        }
    }

    public function test()
    {
        list($uri, $verb) = array_pad($this->args, 2, null);

        if (!$uri) {
            Prompt::outputend(ANSI::fg('You must specify a URI to test.'."\n", ANSI::RED));

            return Help::execute(Application::instance());
        }

        if (!$verb) {
            $verb = 'GET';
        }

        $this->loadRoutes(strtoupper($verb));
        $_SERVER['REQUEST_URI'] = $uri;

        foreach (Router::routes() as $route) {
            if ($route->match()) {
                Prompt::tableHeader([
                    'Verb'  => 10,
                    'Route' => 35,
                    'Name'  => 20,
                    // 'Controller' => 20,
                    // 'Method'     => 20,
                ]);

                return Prompt::tableRow([
                    strtoupper($route->verb),
                    $route->route,
                    $route->name !== $route->route
                        ? $route->name
                        : ' ',
                ]);
            }
        }

        Prompt::outputend(ANSI::fg('Route for URI '.$uri.' could not be found.', ANSI::RED));
        exit;
    }

    private function loadRoutes($verb = null)
    {
        // The router doesn't work without a request method or URI
        $_SERVER['REQUEST_METHOD'] = $verb ? strtoupper($verb) : 'GET';

        // Load the routes file
        require APP_ROOT.'routes.php';
    }
}
