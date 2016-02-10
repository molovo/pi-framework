<?php

namespace Pug\Framework\Session;

use Molovo\Amnesia\Cache\Instance as CacheInstance;
use Molovo\Amnesia\Config as CacheConfig;
use Pug\Crypt\Encrypter;
use Pug\Framework\Config;

class Handler implements \SessionHandlerInterface
{
    private $storage = null;

    public function __construct(Config $config)
    {
        $config        = new CacheConfig($config->toArray());
        $this->storage = new CacheInstance('pug_session_storage', $config);
    }

    public function open($savePath, $sessionName)
    {
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($id)
    {
        if (($payload = $this->storage->get('session.'.$id, false)) !== null) {
            return Encrypter::decrypt($payload);
        };

        return;
    }

    public function write($id, $data)
    {
        if (!empty($data)) {
            $this->storage->set('session.'.$id, Encrypter::encrypt($data));
        }

        return true;
    }

    public function destroy($id)
    {
        $this->storage->clear('session.'.$id);

        return true;
    }

    public function gc($maxlifetime)
    {
        return true;
    }
}
