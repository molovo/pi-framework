<?php

namespace Pug\Cli\Command;

use Molovo\Traffic\Router;
use Pug\Cli\Application;
use Pug\Cli\Interfaces\Command as CommandInterface;
use Pug\Cli\Prompt;

class Routes implements CommandInterface
{
    public static function execute(Application $app)
    {
        // The router doesn't work without a request method
        $_SERVER['REQUEST_METHOD'] = 'GET';

        // Load the routes file
        require APP_ROOT.'routes.php';

        // var_dump(Router::$routes);

        Prompt::tableHeader([
            'Verb'  => 10,
            'Route' => 50,
            'Name'  => 20,
        ]);

        foreach (Router::routes() as $route) {
            $data = [
                $route->verb  => 10,
                $route->route => 50,
            ];

            if ($route->name !== $route->route) {
                $data[$route->name] = 20;
            }

            Prompt::tableRow($data);
        }
    }
}
