<?php
/**
 *
 *
 * @author Khayrattee Wasseem <wasseem@khayrattee.com>
 * @copyright Copyright (c) 2020 Wasseem Khayrattee
 * @license GPL-3.0
 * @link https://7php.com (website)
 */
namespace Wak\Common;

/**
 * Class NativeSession
 * @package Wak\Common
 */
class NativeSession
{
    static protected $sessionIdRegenerated = false;
    static protected $sessionStarted       = false;
    protected $options;

    /**
     * Available options:
     *
     *  * name:     The cookie name (null [omitted] by default)
     *  * id:       The session id (null [omitted] by default)
     *  * lifetime: Cookie lifetime
     *  * path:     Cookie path
     *  * domain:   Cookie domain
     *  * secure:   Cookie secure
     *  * httponly: Cookie http only
     *
     * The default values for most options are those returned by the session_get_cookie_params() function
     *
     * @param array $options  An associative array of session options
     */

    public function __construct(array $options = array())
    {
        $cookieDefaults = session_get_cookie_params();
        $cookieDefaults['lifetime'] = 14400; //4hrs
        $this->options = array_merge(
            array(
                'lifetime' => $cookieDefaults['lifetime'],
                'path'     => $cookieDefaults['path'],
                'domain'   => $cookieDefaults['domain'],
                'secure'   => $cookieDefaults['secure'],
                'httponly' => isset($cookieDefaults['httponly']) ? $cookieDefaults['httponly'] : false,
            ), $options);

        // Skip setting new session name if user don't want it
        if (isset($this->options['name'])) {
            session_name($this->options['name']);
        }
    }

    public function get()
    {
        return $_SESSION;
    }

    public function start()
    {
        if (self::$sessionStarted) {
            return;
        }

        session_set_cookie_params
        (
            $this->options['lifetime'],
            $this->options['path'],
            $this->options['domain'],
            $this->options['secure'],
            $this->options['httponly']
        );

        ///disable native cache limiter as this is managed by HeaderBag directly - applicable if using symfony, not for us
        //session_cache_limiter(false);

        if (!ini_get('session.use_cookies')
            && isset($this->options['id'])
            && $this->options['id']
            && $this->options['id'] != session_id()) {
            session_id($this->options['id']);
        }
        session_start();
        self::$sessionStarted = true;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getId()
    {
        if (!self::$sessionStarted) {
            throw new \Exception('The session must be started before reading its ID');
        }
        return session_id();
    }

    /**
     * Checks if a session key is defined
     *
     * @param string $key The session key
     *
     * @return Boolean true if the session is defined, false otherwise
     */
    public function has($key)
    {
        return array_key_exists($key, $_SESSION);
    }

    /**
     * Reads data from this storage.
     *
     * @param string $key     A unique key identifying your data
     * @param string $default Default value
     *
     * @return mixed Data associated with the key
     */
    public function read($key, $default = null)
    {
        return $this->has($key) ? trim($_SESSION[$key]) : $default;
    }

    /**
     * Writes data to this storage.
     *
     * @param string $key   A unique key identifying your data
     * @param mixed  $data  Data associated with your key
     */
    public function write($key, $data)
    {
        $_SESSION[$key] = $data;
    }

    /**
     * Removes data from this storage.
     *
     * @param  string $key  A unique key identifying your data
     *
     * @return mixed Data associated with the key
     */
    public function remove($key)
    {
        $removedValue = null;
        if (isset($_SESSION[$key])) {
            $removedValue = $_SESSION[$key];
            unset($_SESSION[$key]);
        }
        return $removedValue;
    }

    public function destroy()
    {
        //clear session from globals
        $_SESSION = array();
        //clear session from disk
        session_destroy();
        //remove PHPSESSID from browser
        if (isset( $_COOKIE[session_name()])) {
            setcookie(session_name(), "", time() - 3600, "/");
        }
    }

    /**
     * Regenerates id that represents this storage.
     *
     * @param  Boolean $destroy Destroy session when regenerating?
     *
     * @return Boolean True if session regenerated, false if error
     */
    public function regenerate($destroy = false)
    {
        if (self::$sessionIdRegenerated) {
            return;
        }
        session_regenerate_id($destroy);
        self::$sessionIdRegenerated = true;
    }
}///end class
