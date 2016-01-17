<?php

namespace Pi\Cli\Command;

use Pi\Cli\Interfaces\Command;
use Pi\Cli\StaticCompiler\PageCompiler;

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
        if (isset($args[0]) && $args[0] === self::HELP) {
            return Help::execute($app);
        }

        if (!isset($args[0])) {
            $args[0] = 'all';
        }

        switch ($args[0]) {
            case 'all':
            case 'pages':
                PageCompiler::bootstrap();
                break;
        }

        // The compiler is an extension of the main application,
        // so we bootstrap it in the same way.
        return Compiler::bootstrap();
    }
}
