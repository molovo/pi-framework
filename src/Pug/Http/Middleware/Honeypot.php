<?php

namespace Pug\Http\Middleware;

use Pug\Framework\Application;
use Pug\Http\Exceptions\SuspectedBotException;
use Pug\Http\Interfaces\Middleware as MiddlewareInterface;
use Pug\Http\Request;

class Honeypot implements MiddlewareInterface
{
    /**
     * The name of the hidden input which should be empty.
     */
    const POST_KEY = 'honeypot';

    /**
     * The name of the hidden input containing the time the form was rendered.
     */
    const POST_TIME_KEY = 'honeypot_time';

    /**
     * The minimum time expected to fill out a form (in seconds).
     */
    const MIN_POST_TIME = 1;

    /**
     * Gets the hexadecimal representation of a key.
     *
     * @param string $key The key to convert
     *
     * @return string The converted key
     */
    private static function encode($key)
    {
        $hex = '';
        for ($i = 0; $i < strlen($key); $i++) {
            $hex .= dechex(ord($key[$i]));
        }

        return $hex;
    }

    /**
     * Check that the honeypot field has not been filled in, and that the form
     * was not filled in quicker than possible by a human.
     *
     * @throws SuspectedBotException Thrown if we suspect a bot has posted.
     *
     * @return bool
     */
    public static function check()
    {
        $request = Application::instance()->request;

        // If the honeypot is filled in, throw an exception
        $honey = $request->input->{static::encode(self::POST_KEY)};
        if ($honey) {
            throw new SuspectedBotException;
        }

        $time = $request->input->{static::encode(self::POST_TIME_KEY)};
        if (time() < (base64_decode($time) + self::MIN_POST_TIME)) {
            throw new SuspectedBotException;
        }

        return true;
    }

    /**
     * Render the hidden input used for CSRF protection.
     *
     * @return string The HTML input
     */
    public static function input()
    {
        return '<input type="hidden"
                       name="'.static::encode(self::POST_KEY).'"
                       value="">
                <input type="hidden"
                       name="'.static::encode(self::POST_TIME_KEY).'"
                       value="'.base64_encode(time()).'">';
    }
}
