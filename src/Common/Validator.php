<?php
/**
 *
 * @author Khayrattee Wasseem <wasseem@khayrattee.com>
 * @copyright Copyright (c) 2020 Wasseem Khayrattee
 * @license GPL-3.0
 * @link https://7php.com (website)
 */
namespace Wak\Common;

/**
 * Class Validator
 * @package Wak\Common
 */
class Validator
{
    /**
     * Sanitizes a username, stripping out unsafe characters.
     * Removes tags, octets, entities
     * it will only keep alphanumeric, _ and @
     *
     * @param $username
     * @return bool
     */
    public static function isUsernameValid($username)
    {
        $sanitized_username = FilterClass::doAlphanumericPlus($username);
        return ($username === $sanitized_username);
    }

    /**
     * Checks whether the value is not empty or not null
     *
     * @param $value
     * @return bool
     */
    public static function notEmptyOrNull($value)
    {
        if (is_object($value) && !is_null($value)) {
            return true;
        }
        if (is_array($value)) {
            if (count($value) == 1) { //added on 15th Feb 2016 to cope with [''] and [' '] arrays
                if (self::isAssociative($value)) {
                    return true;
                } elseif (isset($value[0]) && self::notEmptyOrNull($value[0])) {
                    return true;
                }
                return false;
            }
            if (sizeof($value) > 0) {
                return true;
            }
            return true;
        } else {
            if ((is_string($value) || is_int($value)) && ($value != '') && ($value != 'NULL') && (strlen(trim($value)) > 0)) {
                return true;
            }
            return false;
        }
    }

    /**
     * Checks whether the value is empty or null
     *
     * @param $value
     * @return bool
     */
    public static function isEmptyOrNull($value)
    {
        if (is_object($value) && !is_null($value)) {
            return false;
        }
        if (is_array($value)) {
            if (count($value) == 1) { //added on 15th Feb 2016
                if (self::isAssociative($value)) {
                    return false;
                } elseif (isset($value[0]) && self::isEmptyOrNull($value[0])) {
                    return true;
                }
                return false;
            }
            if (sizeof($value) > 0) {
                return false;
            }
            return true;
        } else {
            if ((is_string($value) || is_int($value)) && ($value != '') && ($value != 'NULL') && (strlen(trim($value)) > 0)) {
                return false;
            }
            return true;
        }
    }

    /**
     * @param $email
     * @return string
     */
    public static function isEmailValid($email)
    {
        $sanitized_email = FilterClass::sanitizeEmail($email);
        return ($email === $sanitized_email);
    }

    /**
     * Finds whether a variable is a number or a numeric string
     * and true if it is a floating point or integer value
     * Returns TRUE if var is a number or a numeric string, FALSE otherwise.
     *
     * Further:
     *      is_numeric(-10) will return true whereas 'ctype_digit(-10)' will be false
     *      ctype_digit(12.50) will return false whereas is_numeric(12.50) will be true
     *
     * @param $value
     * @return bool
     */
    public static function isNumeric($value)
    {
        return is_numeric($value);
    }

    /**
     * will tell you if a string contains nothing but numeric characters
     * Returns TRUE if every character in the string text is a decimal digit, FALSE otherwise.
     * E.g:
     *      The string 1820.20 does not consist of all digits.
     *      The string 10002 consists of all digits.
     *      The string wsl!12 does not consist of all digits.
     *
     * Further:
     *      is_numeric(-10) will return true whereas 'ctype_digit(-10)' will be false
     *      ctype_digit(12.50) will return false whereas is_numeric(12.50) will be true
     *
     * @param $value
     * @return bool
     */
    public static function isTrueNumericString($value)
    {
        return ctype_digit($value);
    }

    /**
     * To verify if an array is associative
     *
     * @param $thatArray
     * @return bool
     */
    public static function isAssociative($thatArray)
    {
        foreach ($thatArray as $key => $value) {
            if ($key !== (int) $key) {
                return true;
            }
        }
        return false;
    }
}
