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
    public static function outputEnd($msg)
    {
        echo $msg."\n";
    }

    /**
     * Output the header for a table.
     *
     * @param array $columns The columns in the table
     */
    public static function tableHeader($columns)
    {
        ob_start();
        static::tableRow($columns);
        $row = ob_get_clean();

        static::outputEnd(str_pad('', strlen($row), '='));
        static::output($row);
        static::outputEnd(str_pad('', strlen($row), '='));
    }

    /**
     * Output a row within a table.
     *
     * @param array $columns The columns in the row
     */
    public static function tableRow($columns)
    {
        $output = [];

        foreach ($columns as $column => $length) {
            $output[] = str_pad(' '.$column, $length);
        }

        static::outputEnd(implode('|', $output));
    }
}
