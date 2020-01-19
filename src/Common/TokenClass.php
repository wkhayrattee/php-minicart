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

use Ramsey\Uuid\Uuid;

/**
 * Class TokenClass
 * @package Wak\Common
 */
class TokenClass 
{
    /**
     * some possible password generator
     *
     * @param int $length
     * @param int $level
     * @return string
     */
    public static function generatePassword($length=6,$level=3)
    {
        list($usec, $sec) = explode(' ', microtime());
        srand((float) $sec + ((float) $usec * 100000));

        $validchars[1] = "0123456789abcdfghjkmnpqrstvwxyz";
        $validchars[2] = "0123456789abcdfghjkmnpqrstvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $validchars[3] = "0123456789_!@#$%&*()-=+/abcdfghjkmnpqrstvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_!@#$%&*()-=+/";

        $password  = "";
        $counter   = 0;

        while ($counter < $length) {
            $actChar = substr($validchars[$level], rand(0, strlen($validchars[$level])-1), 1);

            // All character must be different
            if (!strstr($password, $actChar)) {
                $password .= $actChar;
                $counter++;
            }
        }
        return $password;
    }

    /**
     * return random alphanumeric char
     *
     * @return string
     */
    public static function randAlphanumeric()
    {
        $subsets[0] = ['min' => 48, 'max' => 57]; // ascii digits
        $subsets[1] = ['min' => 65, 'max' => 90]; // ascii lowercase English letters
        $subsets[2] = ['min' => 97, 'max' => 122]; // ascii uppercase English letters
        // random choice between lowercase, uppercase, and digits
        $s          = rand(0, 2);
        $ascii_code = mt_rand($subsets[$s]['min'], $subsets[$s]['max']);
        return chr( $ascii_code );
    }

    /**
     * Ramsey Uuid
     *
     * @return string
     * @throws \Exception
     */
    public static function uuid()
    {
        $uuid1 = Uuid::uuid1();
        return trim($uuid1->toString());
    }
}
