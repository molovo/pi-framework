<?php

namespace Pug\Crypt;

class Hash
{
    /**
     * Compare a string to a provided pattern.
     *
     * @param string $pattern The pattern to compare to
     * @param string $str     The string to check
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
        for ($i = 0; $i < $length; $i++) {
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
                $exp .= '[a-z]{1}';
                continue;
            }

            // Use the character literally
            $exp .= $char;
        }

        // Match the string against our regular expression
        return preg_match('#'.$exp.'#i', $str) !== 0;
    }

    /**
     * Create a hash which matches a provided pattern.
     *
     * @param string $pattern The pattern to match
     *
     * @return string The hash
     */
    public static function generate($pattern)
    {
        // Get the length of the pattern
        $length = strlen($pattern);

        // Initialize a variable to build our string into
        $serial = null;

        // Loop through each of the characters in the template
        for ($i = 0; $i < $length; $i++) {
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

            // Replace Xs with random letters
            if ($char === 'X') {
                $serial .= chr(rand(65, 90));
                continue;
            }

            // Add any other character literally
            $serial .= $char;
        }

        return $serial;
    }
}
