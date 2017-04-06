<?php

namespace Pug\Crypt;

class Serial
{
    /**
     * Compare a serial to a provided pattern.
     *
     * @param string $pattern The pattern to compare to
     * @param string $str     The serial to check
     *
     * @return bool
     */
    public static function match($pattern, $str)
    {
        // Get the length of the pattern
        $length = strlen($pattern);

        // Initialize a variable to build our regex into
        $exp = null;

        // Loop through each of the pattern characters
        for ($i = 0; $i < $length; ++$i) {
            // Get the current character
            $char = $pattern[$i];

            // Zeroes match digits
            if ($char === '0') {
                $exp .= '[0-9]{1}';
                continue;
            }

            // Dashes match dashes
            if ($char === '-') {
                $exp .= '-';
                continue;
            }

            // Xs match letters
            if ($char === 'X') {
                $exp .= '[A-Z]{1}';
                continue;
            }

            // Xs match letters
            if ($char === 'y') {
                $exp .= '[a-z]{1}';
                continue;
            }

            // Use the character literally
            $exp .= $char;
        }

        // Match the string against our regular expression
        return preg_match('#'.$exp.'#', $str) !== 0;
    }

    /**
     * Create a serial which matches a provided pattern.
     *
     * @param string $pattern The pattern to match
     *
     * @return string The serial
     */
    public static function generate($pattern)
    {
        // Get the length of the pattern
        $length = strlen($pattern);

        // Initialize a variable to build our string into
        $serial = null;

        // Loop through each of the characters in the template
        for ($i = 0; $i < $length; ++$i) {
            // Get the current character
            $char = $pattern[$i];

            // Replace zeroes with random numbers
            if ($char === '0') {
                $serial .= rand(0, 9);
                continue;
            }

            // Preserve dashes
            if ($char === '-') {
                $serial .= '-';
                continue;
            }

            // Replace Xs with random uppercase letters
            if ($char === 'X') {
                $serial .= chr(rand(65, 90));
                continue;
            }

            // Replace Ys with random lowercase letters
            if ($char === 'y') {
                $serial .= chr(rand(97, 122));
                continue;
            }

            // Add any other character literally
            $serial .= $char;
        }

        return $serial;
    }

    /**
     * Create a unique pattern for a serial.
     *
     * @param mixed $len
     *
     * @return string The pattern
     */
    public static function createPattern($len = 64)
    {
        $opts = ['0', 'X', 'y'];
        $str  = '';

        for ($i = 0; $i < $len; ++$i) {
            $str .= $opts[mt_rand(0, 2)];
        }

        return $str;
    }
}
