<?php

namespace Pi\Cli\Command;

use Pi\Cli\Interfaces\Command;
use Pi\Compiler\Compiler;

class Compile implements Command
{
    /**
     * Execute the command.
     *
     * @param Application $app The application instance
     *
     * @return mixed
     */
    public static function execute($app)
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
            $scopes = ['css'];
        }

        if (!is_array($scopes)) {
            $scopes = [$scopes];
        }

        foreach ($scopes as $scope) {
            $scopeClass = Compiler::$classMap[$scope];
            $config     = $app->config->assets->{$scope};

            if ($config) {
                $config->clean = $app->config->assets->clean;
                $compiler      = new $scopeClass($config);
                $compiler->compile();
            }
        }
    }
}
