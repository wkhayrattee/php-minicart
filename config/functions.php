<?php
/**
 * @author Khayrattee Wasseem <wasseem@khayrattee.com>
 * @copyright Copyright (c) 2020 Wasseem Khayrattee
 * @license GPL-3.0
 * @link https://7php.com (website)
 */

use Pimple\Container;
use Project\NamedPages;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Wak\Common\ConstantList;
use Wak\Common\FilterClass;
use Wak\Common\Utility;
use Wak\Common\Validator;

/**
 * A convenience function to output values of Variables and/or Arrays in a more readable manner
 *
 * @param $array
 * @param bool $die
 * @param bool $print
 * @return string
 */
function p($array, $print = true, $die = false)
{
    $result = '<pre>';
    $result .= print_r($array, true);
    $result .= '</pre><br/>';
    if ($print) {
        echo $result;
        if ($die) {
            die;
        }
    }
    return $result;
}

/**
 * @param $array
 */
function d($array, $print = true)
{
    p($array, $print, true);
}

/**
 * @param $pagename
 * @return mixed
 */
function path($pagename)
{
    $pagename = 'PAGE_' . trim(Utility::strtoupper($pagename));
    return constant('\\Project\\NamedPages::' . $pagename);
}

/**
 * To verify if an array is associative
 *
 * @param $thatArray
 * @return bool
 */
function isAssociative($thatArray)
{
    foreach ($thatArray as $key => $value) {
        if ($key !== (int) $key) {
            return true;
        }
    }
    return false;
}

/**
 * To be used mainly inside Savant TPL as a fast, short method
 * Description: Take a text coming from the database and sanitize it as we should not trust the database as well as a source of getting input
 *
 * @param $text
 * @param bool $remove_breaks optional Whether to remove line breaks and tabs and any left over line breaks and white space chars
 * @return string
 */
function _db($text, $remove_breaks=true)
{
    if (Validator::isEmptyOrNull($text)) {
        return '';
    }
    return FilterClass::sanitizeStringFromDatabase($text, $remove_breaks);
}

/**
 * To be used mainly inside Savant TPL as a fast, short method
 * Description: Take a slug text coming from the database and sanitize it to a clean SLUG as we should not trust the database as well as a source of getting input
 *
 * @param $text
 * @return string
 */
function _slug($text)
{
    if (Validator::isEmptyOrNull($text)) {
        return '';
    }
    return FilterClass::doSanitizeToSlug($text);
}

/**
 * To be used mainly inside Savant TPL as a fast, short method
 * Description: Take a URL text coming from the database and sanitize it to a clean URL as we should not trust the database as well as a source of getting input
 *
 * @param $text
 * @return string
 */
function _url($text)
{
    if (Validator::isEmptyOrNull($text)) {
        return '';
    }
    return FilterClass::doSanitizeURL($text, ['http', 'https']);
}

/**
 * To be used mainly inside Savant TPL as a fast, short method
 * A shorthand for \Wak\Common\Validator::notEmptyOrNull
 *
 * @param $item
 * @return bool
 */
function _is($item)
{
    return Validator::notEmptyOrNull($item);
}

/**
 * Handy to use inside TPL when you need to check if object isset and then if the attribute of that object isset as well, before outputting the value
 * @param $obj
 * @param $class_attribute
 */
function echo_object($obj, $class_attribute, $turn_off_format=false)
{
    if (isset($obj) && isset($obj->{$class_attribute})) {
        if ($turn_off_format == true) {
            echo $obj->{$class_attribute};
        } else {
            echo Utility::formatNumber($obj->{$class_attribute});
        }
    }
}

/**
 * @param $string
 * @param int $decimanlpoints
 * @return string
 */
function _fn($string, $decimanlpoints=2)
{
    return Utility::formatNumber($string, $decimanlpoints);
}

function redirect($page)
{
    $response = new RedirectResponse($page);
    $response->send();
    exit;
}

/**
 * A quick verification if the uuid is a valid one AND of version 1 of Ramsey UUID
 *
 * @param $stringUuid
 * @return bool
 */
function _isValidUuid($stringUuid)
{
    $uuid = \Ramsey\Uuid\Uuid::fromString($stringUuid);
    if ($uuid->getVersion() === \Ramsey\Uuid\Uuid::UUID_TYPE_TIME) {
        return true;
    }
    return false;
}

/**
 * Multi Array in_array() like -- using recursion
 * Get the index at which the value was found, in $index
 * @param $needle
 * @param $haystack
 * @return bool
 */
function in_multi_array($needle, $haystack)
{
    foreach ($haystack as $pos => $val) {
        if (is_array($val)) {
            if (in_multi_array($needle, $val))
                return $pos;
        } else {
            if ($val == $needle) {
                return $pos;
            }
        }
    }
    return false;
}
