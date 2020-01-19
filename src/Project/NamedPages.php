<?php
/**
 * @author Khayrattee Wasseem <wasseem@khayrattee.com>
 * @copyright Copyright (c) 2020 Wasseem Khayrattee
 * @license GPL-3.0
 * @link https://7php.com (website)
 */
namespace Project;

/**
 * Class NamedPages
 *
 * NOTE_TO_SELF: End string with /, ONLY WHEN I know that the URL cannot exists on its own.
 *                  Example: /user/{xyz} ==> here user cannot exist without the variable username
 *
 * @package Project
 */
class NamedPages 
{
    const PAGE_HOME              = '/';
    const PAGE_PRODUCTS          = '/create-product';
    const PAGE_PRODUCT_LIST      = '/products';
    const PAGE_PRICE_RULE_CREATE = '/create-price-rule';
    const PAGE_RULE_LIST         = '/rules';
    const PAGE_CART              = '/cart';
    const PAGE_INVOICE           = '/invoice/';
}
