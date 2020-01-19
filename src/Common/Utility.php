<?php
/**
 * @author Khayrattee Wasseem <wasseem@khayrattee.com>
 * @copyright Copyright (c) 2020 Wasseem Khayrattee
 * @license GPL-3.0
 * @link https://7php.com (website)
 */
namespace Wak\Common;

/**
 * Class C
 * @package Wak\Common
 */
class Utility
{
    /**
     * When I want to find let' say a value 25 exists in an array (can be multi) with key ID
     * Also, if $returnItemMatch passed as the 4th param, return the matched item as referenced
     *
     * @param $array
     * @param $key
     * @param $val
     * @param null $returnItemMatch
     * @return bool
     */
    public static  function find_key_value($array, $key, $val, &$returnItemMatch=null)
    {
        foreach ($array as $item) {
            if (is_array($item) && self::find_key_value($item, $key, $val)) {
                $returnItemMatch = $item;
                return true;
            }
            if (isset($item[$key]) && $item[$key] == $val) {
                $returnItemMatch = $item;
                return true;
            }
        }
        return false;
    }

    /**
     * @param $money
     * @return string
     */
    public static function formatMoney($money, $decimal=2, $decimal_separator=',', $thousands_sep=' ')
    {
        return number_format($money, $decimal, $decimal_separator, $thousands_sep);
    }

    /**
     * @param $money
     * @param int $decimanlpoints
     * @return string
     */
    public static function formatNumber($money, $decimanlpoints=0)
    {
        return number_format($money, $decimanlpoints, ',', ' ');
    }

    /**
     * @param bool $displayWithTime
     * @return bool|string
     */
    public static function datenow($displayWithTime=true)
    {
        if($displayWithTime === true) {
            return date(ConstantList::DATE_FORMAT_MYSQL_DATETIME);
        } else {
            return date(ConstantList::DATE_FORMAT_MYSQL_DATETIME_WITHOUT_TIME);
        }
    }

    /**
     * @return bool|string
     */
    public static function timenow()
    {
        return date(ConstantList::DATE_FORMAT_TIME_ONLY);
    }

    /**
     * if the file does not exist, it will create it
     *
     * @param $fullPathToFileName
     * @param $string_to_save
     * @return bool
     */
    public static function WriteMsgToFile($fullPathToFileName, $string_to_save)
    {
        $handle = @fopen($fullPathToFileName, 'a+');
        //$tempFile = file_get_contents($handle);
        if ($handle === false) {
            return false;
        }
        else {
            @fwrite($handle, $string_to_save."\r\n");
            fclose($handle);
            return true;
        }
    }

    /**
     * @param $error_msg_string
     * @return string
     */
    public static function BuildErrorMessage($error_msg_string)
    {
        $tmp_string = "===================== \r\n";
        $tmp_string .= "DATE: \r". date('Y-m-d H:m:s') . " \r\n";
        $tmp_string .= "PAGE: \r". $_SERVER['SCRIPT_FILENAME'] . " \r\n";
        $tmp_string .= "ERROR: \r". $error_msg_string;
        $tmp_string .= "\r\nBACK_TRACE: \r\n". var_export(debug_backtrace(), true);
        $tmp_string .= "\r\n=====================";
        return $tmp_string;
    }

    /**
     * @param $error_msg_string
     * @return string
     */
    public static function BuildErrorMessage_Simple($error_msg_string)
    {
        $tmp_string = "===================== \r\n";
        $tmp_string .= "DATE: \r". date('Y-m-d H:m:s') . " \r\n";
        $tmp_string .= "PAGE: \r". $_SERVER['SCRIPT_FILENAME'] . " \r\n";
        $tmp_string .= "ERROR: \r". $error_msg_string;
        $tmp_string .= "\r\n=====================";
        return $tmp_string;
    }

    /**
     * @param $error_msg_string
     * @return string
     */
    public static function BuildErrorMessageForWeb($error_msg_string)
    {
        $tmp_string = "<br/>=====================<br/>";
        $tmp_string .= "DATE: <br/>". date('Y-m-d H:m:s') . "<br/>";
        $tmp_string .= "PAGE: <br/>". $_SERVER['SCRIPT_FILENAME'] . " <br/>";
        $tmp_string .= "ERROR: <br/>". $error_msg_string;
        $tmp_string .= "<br/>=====================<br/><br/>";
        return $tmp_string;
    }

    /**
     * for $_POST
     *
     * @param $submit
     * @return bool
     */
    public static function isPostSubmit($submit)
    {
        return ( isset($_POST[$submit]) || isset($_POST[$submit.'_x']) || isset($_POST[$submit.'_y']) );
    }

    /**
     * for $_GET
     *
     * @param $submit
     * @return bool
     */
    public static function isGetSubmit($submit)
    {
        return ( isset($_GET[$submit]) || isset($_GET[$submit.'_x']) || isset($_GET[$submit.'_y']) );
    }

    /**
     * @param $str
     * @return bool|string
     */
    public static function strtolower($str)
    {
        if (is_array($str)) {
            return false;
        }
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($str, 'utf-8');
        }
        return strtolower($str);
    }

    /**
     * @param $str
     * @return bool|string
     */
    static function strtoupper($str)
    {
        if (is_array($str)) {
            return false;
        }
        if (function_exists('mb_strtoupper')) {
            return mb_strtoupper($str, 'utf-8');
        }
        return strtoupper($str);
    }
}
