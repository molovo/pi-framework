<?php

namespace Pug\Crypt;

use Pug\Framework\Config;

class Encrypter
{
    public static function encrypt($value, $cipher = MCRYPT_RIJNDAEL_256)
    {
        $encrypter = new self($cipher);

        return $encrypter->getEncryptedValue($value);
    }

    public static function decrypt($value, $cipher = MCRYPT_RIJNDAEL_256)
    {
        $encrypter = new self($cipher);

        return $encrypter->getDecryptedValue($value);
    }

    public function __construct($cipher = MCRYPT_RIJNDAEL_256)
    {
        $this->cipher = mcrypt_module_open($cipher, '', 'cbc', '');
        $this->key    = Config::get('app.secret');
    }

    public function randomBytes($length = 32)
    {
        return openssl_random_pseudo_bytes(32);
    }

    public function getEncryptedValue($decrypted)
    {
        $iv = $this->randomBytes();
        mcrypt_generic_init($this->cipher, $this->key, $iv);
        $encrypted = mcrypt_generic($this->cipher, $decrypted);
        mcrypt_generic_deinit($this->cipher);

        $payload = [
            'iv'     => base64_encode($iv),
            'value'  => base64_encode($encrypted),
            'length' => strlen($decrypted),
        ];

        return base64_encode(json_encode($payload));
    }

    public function getDecryptedValue($payload)
    {
        $payload = json_decode(base64_decode($payload));

        $iv        = base64_decode($payload->iv);
        $encrypted = base64_decode($payload->value);
        $length    = $payload->length;

        mcrypt_generic_init($this->cipher, $this->key, $iv);
        $decrypted = mdecrypt_generic($this->cipher, $encrypted);
        mcrypt_generic_deinit($this->cipher);

        return substr($decrypted, 0, $length);
    }
}
