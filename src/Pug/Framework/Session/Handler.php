<?php

namespace Pug\Framework\Session;

use Molovo\Amnesia\Cache\Instance as CacheInstance;
use Molovo\Amnesia\Config as CacheConfig;
use Pug\Crypt\Encrypter;
use Pug\Framework\Config;

class Handler implements \SessionHandlerInterface
{
    /**
     * The cache instance in which session data will be stored.
     *
     * @var CacheInstance
     */
    private $storage = null;

    /**
     * Create the session handler.
     *
     * @param Config $config The session config
     */
    public function __construct(Config $config)
    {
        // Convert the config object into one the cache understands
        $config = new CacheConfig($config->toArray());

        // Create the cache instance for use in reading/writing session data
        $this->storage = new CacheInstance('pug_session_storage', $config);
    }

    /**
     * @param string $savePath    The session save path (Not used).
     * @param string $sessionName The session name (Not used).
     *
     * @return bool Check that the cache instance has been created successfully
     */
    public function open($savePath, $sessionName)
    {
        return ($this->storage instanceof CacheInstance);
    }

    /**
     * Close the session (Not used).
     *
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * Retrieve the session payload from the cache, and decrypt it.
     *
     * @param string $id The ID of the current session
     *
     * @return string The serialized session data
     */
    public function read($id)
    {
        if (($payload = $this->storage->get('session.'.$id, false)) !== null) {
            return Encrypter::decrypt($payload);
        };

        return;
    }

    /**
     * Encrypt the session data, and store it in the cache.
     *
     * @param string $id   The ID of the current session
     * @param string $data The serialized session data
     *
     * @return bool
     */
    public function write($id, $data)
    {
        if (!empty($data)) {
            $this->storage->set('session.'.$id, Encrypter::encrypt($data));
        }

        return true;
    }

    /**
     * Clear the session data from the cache.
     *
     * @param string $id The ID of the current session
     *
     * @return bool
     */
    public function destroy($id)
    {
        $this->storage->clear('session.'.$id);

        return true;
    }

    /**
     * Garbage collection (Not used).
     *
     * @param int $maxlifetime
     *
     * @return bool
     */
    public function gc($maxlifetime)
    {
        return true;
    }
}
