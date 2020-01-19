<?php
/**
 * Listing of all our ROUTES
 * On LIVE, caching of routes will be enabled
 *
 * @author Khayrattee Wasseem <wasseem@khayrattee.com>
 * @copyright Copyright (c) 2020 Wasseem Khayrattee
 * @license GPL-3.0
 * @link https://7php.com (website)
 */
return FastRoute\cachedDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', path('home'), ['Controller\IndexAction', 'index']);
    $r->addRoute(['GET','POST'], path('products') . '[/]', ['Controller\ProductsAction', 'products']);
    $r->addRoute(['GET','POST'], path('product_list') . '[/[{page:\d+}[/]]]', ['Controller\ProductListAction', 'product_list']);
    $r->addRoute(['GET','POST'], path('price_rule_create') . '[/]', ['Controller\PriceRuleCreateAction', 'price_rule_create']);
    $r->addRoute(['GET','POST'], path('rule_list') . '[/[{page:\d+}[/]]]', ['Controller\RuleListAction', 'rule_list']);
    $r->addRoute(['GET','POST'], path('cart') . '[/]', ['Controller\CartAction', 'cart']);
    $r->addRoute(['GET','POST'], path('invoice') . '{cart_id:[a-zA-Z0-9+_\-\.]+}' . '[/]', ['Controller\InvoiceAction', 'invoice']);

}, [
    'cacheFile'     => CACHE_FOLDER . '/route.cache', /* required */
    'cacheDisabled' => IS_DEBUG_ENABLED,
]);
