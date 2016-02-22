<?php

namespace Pug\Cli\Command;

use Pug\Cli\Application;
use Pug\Cli\Interfaces\Command as CommandInterface;
use Pug\Crypt\Serial;

class Secure implements CommandInterface
{
    /**
     * Execute the command.
     *
     * @param Application $app The application instance
     *
     * @return mixed
     */
    public static function execute(Application $app)
    {
        $command = new static($app);
        $command->generateSecret();
        $command->generateSessionSerial();
    }

    /**
     * Create a new instance of the command.
     *
     * @param Application $app The application instance
     */
    public function __construct(Application $app)
    {
        $this->args  = $app->args;
        $this->scope = isset($this->args[0]) ? array_shift($this->args) : '';

        if ($this->scope === 'help') {
            Help::execute($app);
            exit;
        }
    }

    /**
     * Generate a new secret key for encryption purposes.
     *
     * @return string The secret key
     */
    public function generateSecret()
    {
        $filename = APP_ROOT.'config/app.yaml';

        // Get the contents of the config file
        $yaml = file_get_contents($filename);

        // Generate a secure random string
        $result_is_strong = false;
        while (!$result_is_strong) {
            $secret = bin2hex(openssl_random_pseudo_bytes(16, $result_is_strong));
        }

        // Replace the secret value in the config file with the new string
        $regex   = '/^\s*secret:\s*.*/m';
        $replace = "secret: $secret";
        $yaml    = preg_replace($regex, $replace, $yaml);

        // Store the new YAML content in the config file
        file_put_contents($filename, $yaml);
    }

    /**
     * Generate a new serial format for session IDs.
     *
     * @return string The serial
     */
    public function generateSessionSerial()
    {
        $filename = APP_ROOT.'config/session.yaml';

        // Get the contents of the config file
        $yaml = file_get_contents($filename);

        // Create a pattern for the session ID
        $serial = Serial::createPattern(64);

        // Replace the id_format value in the config file with the new pattern
        $regex   = '/^\s*id_format:\s*.*/m';
        $replace = "id_format: $serial";
        $yaml    = preg_replace($regex, $replace, $yaml);

        // Store the new YAML content in the config file
        file_put_contents($filename, $yaml);
    }
}
