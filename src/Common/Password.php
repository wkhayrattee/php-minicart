<?php
/**
 * REF: http://jeremykendall.net/2014/01/04/php-password-hashing-a-dead-simple-implementation/
 *
 * @author Khayrattee Wasseem <wasseem@khayrattee.com>
 * @copyright Copyright (c) 2020 Wasseem Khayrattee
 * @license GPL-3.0
 * @link https://7php.com (website)
 */
namespace Wak\Common;

/**
 * Class Password
 * @package Wak\Common
 */
class Password 
{
    const PASSWORD_INVALID          = 0;
    const PASSWORD_VALID            = 1;
    const PASSWORD_NEEDS_REHASH     = 2;

    private $password;
    private $hash;
    private $algo;
    private $cost;

    /**
     * Provide the (user) clear text provided password
     *
     * @param $password
     * @param null $hash
     */
    public function __construct($password, $hash = null)
    {
        $this->password = $password;
        $this->hash = $hash;
        $this->algo = PASSWORD_BCRYPT; //60 characters in length
        $this->cost = 7;
    }

    /**
     * @param int $cost
     * @param int $algo
     * @return bool|string
     */
    public function getHash($cost=7, $algo = PASSWORD_BCRYPT)
    {
        $this->algo = $algo;
        $this->cost = $cost;
        return password_hash($this->password, $this->algo, ['cost' => $this->cost]);
    }

    /**
     * @param $password
     * @param int $cost
     * @param int $algo
     * @return bool|string
     */
    public static function hashThisPassword($password, $cost=7, $algo = PASSWORD_BCRYPT)
    {
        return password_hash($password, $algo, ['cost' => $cost]);
    }

    /**
     * @param bool $checkRehash
     * @return int
     */
    public function isValid($checkRehash = false)
    {
        if (password_verify($this->password, $this->hash)) {
            if($checkRehash && password_needs_rehash($this->hash, $this->algo, ['cost' => $this->cost])) {
                return self::PASSWORD_NEEDS_REHASH;
            }
            return self::PASSWORD_VALID;
        }
        return self::PASSWORD_INVALID;
    }
}
