<?php

namespace Pug\Http\Middleware;

use Molovo\Str\Str;
use Pug\Framework\Application;
use Pug\Framework\Session;
use Pug\Http\Exceptions\CSRFMismatchException;
use Pug\Http\Interfaces\Middleware as MiddlewareInterface;
use Pug\Http\Request;

class Csrf implements MiddlewareInterface
{
    /**
     * The key used for posting CSRF tokens.
     */
    const POST_KEY = '_csrf';

    /**
     * The key used for storing CSRF tokens in the session.
     */
    const SESSION_KEY = 'csrf_token';

    /**
     * The CSRF token for this request.
     *
     * @var string|null
     */
    private static $token = null;

    /**
     * Check that the posted CSRF token matches the value stored in the session.
     *
     * @throws CSRFMismatchException Thrown if CSRF tokens do not match.
     *
     * @return bool
     */
    public static function check()
    {
        $request = Application::instance()->request;

        $key    = $request->input->{self::POST_KEY};
        $stored = Session::get(self::SESSION_KEY);

        if ($request->isPost() && $key !== $stored) {
            throw new CSRFMismatchException('CSRF token is invalid');
        }

        return true;
    }

    /**
     * Retrieve the CSRF token for the current request.
     *
     * @return string The CSRF token
     */
    private static function token()
    {
        if (static::$token !== null) {
            return static::$token;
        }

        $token = Str::random(32);

        // Register a shutdown function to store the new token
        register_shutdown_function(function () use ($token) {
            Session::set(self::SESSION_KEY, $token);
        });

        return static::$token = $token;
    }

    /**
     * Render the hidden input used for CSRF protection.
     *
     * @return string The HTML input
     */
    public static function input()
    {
        return '<input type="hidden"
                       name="'.self::POST_KEY.'"
                       value="'.static::token().'">';
    }
}
