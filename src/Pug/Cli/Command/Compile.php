<?php

namespace Pug\Cli\Command;

use Pug\Cli\Application;
use Pug\Cli\Interfaces\Command;
use Pug\Cli\Prompt;
use Pug\Cli\Prompt\ANSI;
use Pug\Compiler\Compiler;

class Compile implements Command
{
    /**
     * Execute the command.
     *
     * @param Application $app The application instance
     *
     * @return mixed
     */
    public static function execute(Application $app)
    {
        $args = $app->args;

        if (!isset($args[0])) {
            $args[0] = 'all';
        }

        $scope = $args[0];

        if ($scope === self::HELP) {
            return Help::execute($app);
        }

        $scopes = [];

        if ($scope === 'all') {
            $scopes = array_keys(Compiler::$classMap);
        }

        if ($scope === 'assets') {
            $scopes = ['css', 'coffee', 'js', 'sass'];
        }

        if (!is_array($scopes)) {
            $scopes = [$scopes];
        }

        foreach ($scopes as $scope) {
            $scopeClass = Compiler::$classMap[$scope];

            if ($scope === 'pages') {
                $msg = 'Compiling '.ucwords($scope).'...';
                Prompt::output(ANSI::fg(str_pad($msg, 22), ANSI::GRAY));
                $scopeClass::bootstrap();
                Prompt::outputend(ANSI::fg(' done', ANSI::GREEN));
                continue;
            }

            $config = $app->config->assets->{$scope};

            if ($config) {
                $config->clean = $app->config->assets->clean;
                $compiler      = new $scopeClass($config);
                $msg           = 'Compiling '.ucwords($scope).'...';
                Prompt::output(ANSI::fg(str_pad($msg, 22), ANSI::GRAY));
                $compiler->compile();
                Prompt::outputend(ANSI::fg(' done', ANSI::GREEN));
            }
        }
    }
}
