<?php

namespace Pug\Framework;

class Environment
{
    /**
     * Check if the current environment matches one of the names passed.
     *
     * @param string|string[] $names A name or array of names
     *
     * @return bool
     */
    public static function is($names)
    {
        if (is_string($names)) {
            $names = [$names];
        }

        $app = Application::instance();

        foreach ($names as $name) {
            if (in_array($name, $app->environments)) {
                return true;
            }
        }

        return false;
    }
}
