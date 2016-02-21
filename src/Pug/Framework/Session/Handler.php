<?php

namespace Pug\Framework\Session;

use Molovo\Amnesia\Cache\Instance as CacheInstance;
use Molovo\Amnesia\Config as CacheConfig;
use Pug\Crypt\Encrypter;
use Pug\Crypt\Hash;
use Pug\Framework\Config;
use Pug\Http\Cookie;

class Handler implements \SessionHandlerInterface
{
    /**
     * A default session ID format - should be overridden.
     */
    const DEFAULT_ID_FORMAT = 'XX00-XX00-00XX-00XX-XX00-XX00-XXXX-97XX-XX00-Y0Y0';

    /**
     * The cache instance in which session data will be stored.
     *
     * @var CacheInstance
     */
    private $storage = null;

    /**
     * The session config.
     *
     * @var Config
     */
    private $config = null;

    /**
     * Create the session handler.
     *
     * @param Config $config The session config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;

        // Convert the config object into one the cache understands
        $cacheConfig = new CacheConfig($config->toArray());

        // Create the cache instance for use in reading/writing session data
        $this->storage = new CacheInstance('pug_session_storage', $cacheConfig);

        if (Cookie::get($config->cookie_name) === null) {
            $this->generateId();
        }
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
        $this->checkId($id);

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
        $this->checkId($id);

        if (!empty($data)) {
            $this->storage->set('session.'.$id, Encrypter::encrypt($data), $this->config->lifetime);
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
        $this->checkId($id);

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

    /**
     * Check that the session ID conforms to the defined ID format.
     *
     * @param string $id The ID to check
     *
     * @return bool
     */
    private function checkId($id)
    {
        if (Cookie::get($this->config->cookie_name) === null) {
            return true;
        }

        if (!Hash::match($this->config->id_pattern, $id)) {
            // The session ID does not match the provided pattern, so it's
            // possible that this is a hijack attempt. To avoid this, we create
            // a new session ID, and remove the old one from the cookie.
            Cookie::set($this->config->cookie_name, null);
            session_regenerate_id(Hash::generate($this->config->id_pattern));
        }

        return true;
    }

    /**
     * Generate a new session ID.
     *
     * @return bool
     */
    private function generateId()
    {
        return session_id(Hash::generate($this->config->id_format ?: self::DEFAULT_ID_FORMAT));
    }
}
