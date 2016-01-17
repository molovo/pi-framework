<?php

namespace Pi\Cli\Interfaces;

use Pi\Cli\Application;

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
    public static function execute($app);
}
