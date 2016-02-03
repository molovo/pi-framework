<?php

namespace Pug\Cli\Interfaces;

use Pug\Cli\Application;

interface Command
{
    const HELP = 'help';

    /**
     * Execute the command.
     *
     * @param Application $app The application instance
     *
     * @return mixed
     */
    public static function execute(Application $app);
}
