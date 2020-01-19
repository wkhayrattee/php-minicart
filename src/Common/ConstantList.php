<?php
/**
 * List of Constants to be used application wide
 *
 * @author Khayrattee Wasseem <wasseem@khayrattee.com>
 * @copyright Copyright (c) 2020 Wasseem Khayrattee
 * @license GPL-3.0
 * @link https://7php.com (website)
 */
namespace Wak\Common;

/**
 * Class ConstantList
 * @package Wak\Common
 */
class ConstantList
{
    // 2001-03-10 17:16:18 (the MySQL DATETIME format)
    const DATE_FORMAT_MYSQL_DATETIME                = 'Y-m-d H:i:s';
    const DATE_FORMAT_MYSQL_DATETIME_WITHOUT_TIME   = 'Y-m-d';

    // e.g 17:16:18
    const DATE_FORMAT_TIME_ONLY = 'H:i:s';

    const ENUM_DISPLAY_NONE     = 'display:none';
    const ENUM_DISPLAY_BLOCK    = 'display:block';
    const ENUM_DISPLAY_INLINE   = 'display:inline';
    const ENUM_DISPLAY_         = '';

    const ENUM_STATE_DIV_SUCCESS = 'alert alert-success';
    const ENUM_STATE_DIV_INFO    = 'alert alert-info';
    const ENUM_STATE_DIV_WARNING = 'alert alert-warning';
    const ENUM_STATE_DIV_ERROR   = 'alert alert-danger';

    ///<editor-fold desc="CLASS NAMES">
    const CLASS_NATIVE_SESSION = "NativeSession";
    const CLASS_SCRIPT_HANDLER = "ScriptHandler";
    ///</editor-fold>

    ///<editor-fold desc="SESSION">
    const KEY_SESSION_ACCESS_LEVEL  = 'ACCESS_LEVEL';
    ///</editor-fold>

    const KEY_SESSION_CART_ID           = 'cart_id';
    const KEY_SESSION_CHECKOUT_STATUS   = 'cart_checkout_status';

    ///<editor-fold desc="DB TABLE names">
    const DB_TABLE_PRODUCT           = 'product';
    const DB_TABLE_PRICING_RULES    = 'pricing_rules';
    const DB_TABLE_CART             = 'cart';
    const DB_TABLE_CART_ITEM        = 'cart_item';
    ///</editor-fold>
}
