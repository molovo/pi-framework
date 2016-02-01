<?php

namespace Pug\Cli\Prompt;

class ANSI
{
    /**
     * Color Constants.
     */
    const BLACK   = 0;
    const RED     = 1;
    const GREEN   = 2;
    const YELLOW  = 3;
    const BLUE    = 4;
    const MAGENTA = 5;
    const CYAN    = 6;
    const WHITE   = 7;
    const GRAY    = '8;5;242';

    /**
     * Applies ANSI foreground color codes to a string.
     *
     * @param string     $str   The string to colorize
     * @param int|string $color The color
     *
     * @return string The string with ANSI color escaping applied
     */
    public static function fg($str, $color = self::WHITE)
    {
        return "\e[0;3".$color.'m'.$str."\e[0;m";
    }

    /**
     * Applies ANSI background color codes to a string.
     *
     * @param string     $str   The string to colorize
     * @param int|string $color The color
     *
     * @return string The string with ANSI color escaping applied
     */
    public static function bg($str, $color = self::WHITE)
    {
        return "\e[0;4".$color.'m'.$str."\e[0;m";
    }

    /**
     * Applies ANSI bold escape codes to a string.
     *
     * @param string $str The string to convert
     *
     * @return string The string with escape codes applied
     */
    public static function bold($str)
    {
        return "\e[0;1m".$str."\e[0;22m";
    }

    /**
     * Applies ANSI italic escape codes to a string.
     *
     * @param string $str The string to convert
     *
     * @return string The string with escape codes applied
     */
    public static function italic($str)
    {
        return "\e[0;3m".$str."\e[0;23m";
    }

    /**
     * Applies ANSI underline escape codes to a string.
     *
     * @param string $str The string to convert
     *
     * @return string The string with escape codes applied
     */
    public static function underline($str)
    {
        return "\e[0;4m".$str."\e[0;24m";
    }

    /**
     * Applies ANSI inverse escape codes to a string.
     *
     * @param string $str The string to convert
     *
     * @return string The string with escape codes applied
     */
    public static function inverse($str)
    {
        return "\e[0;7m".$str."\e[0;27m";
    }

    /**
     * Applies ANSI strikethrough escape codes to a string.
     *
     * @param string $str The string to convert
     *
     * @return string The string with escape codes applied
     */
    public static function strikethrough($str)
    {
        return "\e[0;9m".$str."\e[0;29m";
    }
}
