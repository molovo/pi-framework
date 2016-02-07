<?php

namespace Pug\Cli;

use Pug\Cli\Prompt\ANSI;

class Prompt
{
    /**
     * An array of column lengths used to align rows in a table.
     *
     * @var int[]
     */
    private static $tableColumnLengths = [];

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
    public static function outputEnd($msg)
    {
        echo $msg."\n";
    }

    /**
     * Output the header for a table.
     *
     * @param array $columns The columns in the table
     */
    public static function tableHeader(array $columns)
    {
        ob_start();
        static::$tableColumnLengths = array_values($columns);
        static::tableRow(array_keys($columns));
        $row = ob_get_clean();

        $separator = ANSI::fg(str_pad('', array_sum(static::$tableColumnLengths), '='), ANSI::GRAY);
        static::outputEnd($separator);
        static::output($row);
        static::outputEnd($separator);
    }

    /**
     * Output a row within a table.
     *
     * @param array $columns The columns in the row
     */
    public static function tableRow(array $columns)
    {
        $output = [];

        foreach ($columns as $index => $value) {
            $output[] = str_pad(' '.$value, static::$tableColumnLengths[$index]);
        }

        static::outputEnd(implode(ANSI::fg('|', ANSI::GRAY), $output));
    }
}
