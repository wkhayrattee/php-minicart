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

use Wak\Common\Vendors\Wordpress;

/**
 * Class FilterClass
 * @package Wak\Common
 */
class FilterClass extends Wordpress
{
    /**
     * Sanitizes a username, stripping out unsafe characters.
     * Removes tags, octets, entities
     * Will keep only alphanumeric
     *
     * @param $data
     * @return string
     */
    public static function doAlphanumeric($data)
    {
        return self::sanitize_alphanumeric($data);
    }

    /**
     * Sanitizes a username, stripping out unsafe characters.
     * Removes tags, octets, entities
     * But it will only keep alphanumeric, _ and @
     *
     * @param $data
     * @return mixed|string
     */
    public static function doAlphanumericPlus($data)
    {
        return self::sanitize_to_alphanumeric_plus($data);
    }

    /**
     * Sanitizes a username, stripping out unsafe characters.
     * Removes tags, octets, entities
     * Will keep only alphanumeric, _, SPACE, ., -, and @
     *
     * @param $data
     * @return string
     */
    public static function doAlphanumericPlusPlus($data)
    {
        return self::sanitize_user($data);
    }

    /**
     * Removes tags, octets, entities,
     * will only keep:
     * alphanumeric, _, space, ., -, @, ', ", `, &, ?, !, :, *, $, +, (), {}, {}
     * SHOULD NOT
     *     - accept <> because this could create a tag or octet
     *
     * @param $data
     * @return mixed|string
     */
    public static function doSanitizeTitle($data)
    {
        return self::sanitize_title($data);
    }

    /**
     * Keep everything, EXCEPT tags, octets, entities
     *
     * @param $data
     * @return mixed|string
     */
    public static function doStripAllTags($data)
    {
        return self::sanitize_message($data);
    }

    /**
     * Sanitizes a string, replacing whitespace and a few other characters with dashes.
     * Limits the output to alphanumeric characters, underscore (_) and dash (-).
     * and finally Whitespace becomes a dash.
     *
     * @param $data
     * @return string
     */
    public static function doSanitizeToSlug($data)
    {
        return self::sanitize_title_with_dashes($data);
    }

    /**
     * Sanitizes a URL string
     *
     * @param $unclean_url
     * @param null $protocols
     * @param string $_context
     * @return string
     */
    public static function doSanitizeURL($unclean_url, $protocols = null, $_context = 'display')
    {
        return self::esc_url($unclean_url, $protocols, $_context);
    }

    /**
     * Sanitizes a filename, replacing whitespace with dashes.
     * Removes special characters that are illegal in filenames on certain
     * operating systems and special characters requiring special escaping
     * to manipulate at the command line. Replaces spaces and consecutive
     * dashes with a single dash. Trims period, dash and underscore from beginning
     * and end of filename.
     *
     * @param $filename
     * @return string
     */
    public static function sanitizeFileName($filename)
    {
        return self::sanitize_file_name($filename);
    }

    /**
     * Sanitize a string from user input or from the db
     * check for invalid UTF-8,
     * Convert single < characters to entity,
     * strip all tags,
     * remove line breaks, tabs and extra white space,
     * strip octets.
     *
     * @param $input
     * @param bool $remove_breaks optional Whether to remove line breaks and tabs and any left over line breaks and white space chars
     * @return string
     */
    public static function sanitizeStringFromDatabase($input, $remove_breaks=false)
    {
        return self::sanitize_text_field($input, $remove_breaks);
    }

    /**
     * Use HTMLpurifier
     * REF: http://htmlpurifier.org/
     *
     * @param $dirty_html
     * @return string
     */
    public static function doHTMLpurifier($dirty_html)
    {
        $purifier = new \HTMLPurifier();
        return $purifier->purify($dirty_html);
    }

    /**
     * @param $email
     * @return string
     */
    public static function sanitizeEmail($email)
    {
        return self::sanitize_email($email);
    }

    /**
     * Converts a number of special characters into their HTML entities.
     * Specifically deals with: &, <, >, ", and '.
     * Will encode both " and '
     *
     * @param $data
     * @return string
     */
    public static function encodeHTML($data)
    {
        return self::_wp_specialchars($data, ENT_QUOTES, "UTF-8", true);
    }

    /**
     * Converts a number of HTML entities into their special characters.
     * Specifically deals with: &, <, >, ", and '.
     * Will decode both " and '
     *
     * @param $data
     * @return string
     */
    public static function decodeHTML($data)
    {
        return self::wp_specialchars_decode($data);
    }
}
