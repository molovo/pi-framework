<?php

namespace Pug\Cli;

class Prompt
{
    /**
     * Output a string.
     *
     * @param string $msg The string to output
     */
    public static function output($msg)
    {
        echo $msg;
    }

    /**
     * Output a string with a trailing newline.
     *
     * @param string $msg The string to output
     */
    public static function outputend($msg)
    {
        echo $msg."\n";
    }
}
