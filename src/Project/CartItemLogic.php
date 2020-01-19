<?php
/**
 * Handles the business logic for the cart
 *
 * @author Khayrattee Wasseem <wasseem@khayrattee.com>
 * @copyright Copyright (c) 2020 Wasseem Khayrattee
 * @license GPL-3.0
 * @link https://7php.com (website)
 */
namespace Project;


use Model\CartItemsDal;
use Pimple\Container;
use Wak\Common\Utility;

class CartItemLogic
{
    public $cart_id;
    public $sku;
    public $prod_qty;
    public $unit_price;
    public $date_created;
    public $total_price;
    public $container;
    public $is_discounted;
    /**
     * will contain list of discount, with SKU as KEY
     * @var \stdClass $discount_list
     */
    public $discount_list;
    /**
     * will contain the current similar item that is already in the cart
     * @var \stdClass $similar_item_in_cart
     */
    public $similar_item_in_cart;
    /**
     * @var boolean $do_insert
     */
    public $do_insert;

    /**
     * We will initiaze our properties here
     *
     * CartItemLogic constructor.
     * @param $cart_stdObject
     * @param Container $container
     * @throws \Exception
     */
    public function __construct($cart_stdObject, Container $container)
    {
        if (is_object($cart_stdObject)) {
            $this->cart_id      = $cart_stdObject->cart_id;
            $this->sku          = $cart_stdObject->sku;
            $this->prod_qty     = $cart_stdObject->prod_qty;
            $this->date_created = Utility::datenow();
            $this->unit_price   = $cart_stdObject->unit_price;
        } else {
            throw new \Exception('Cannot fetch the item detail entered');
        }
        //Initialize these
        $this->container            = $container;
        $this->is_discounted        = 0;
        $this->total_price          = 0;
        $this->discount_list        = null;
        $this->similar_item_in_cart = null;
        $this->do_insert            = true;
    }

    public function addToCart()
    {
        /**
         * Process the PROMO
         * ReCalculate pricing for this item for the qty ordered
         * Increment QTY & upd in db
         * Upd new pricing + upd is_discounted to true
         * Save in DB
         */

        //check if PROMO for this SKU
        $this->fetchPricingRules_bySku(); //will also set $this->is_discounted accordingly

        //Check if we need to do an INSERT or an UPDATE
        $this->fetchSameItemInThisCart_bySku(); //will also set $this->do_insert accordingly

        if ($this->do_insert !== true) {
            //there is already same item in cart, so just adjust qty now
            $this->prod_qty = ($this->prod_qty + $this->similar_item_in_cart->prod_qty);
        }
        //Process the PROMO
        $this->doApplicablePricingRule($this->prod_qty);

        $cartItemObject                 = new \stdClass();
        $cartItemObject->cart_id        = $this->cart_id;
        $cartItemObject->sku            = $this->sku;
        $cartItemObject->prod_qty       = $this->prod_qty;
        $cartItemObject->is_discounted  = $this->is_discounted;
        $cartItemObject->total_price    = $this->total_price;
        $cartItemObject->date_created   = Utility::datenow();

        //Save in DB
        if ($this->do_insert === true) {
            CartItemsDal::insertCartItem($this->container, $cartItemObject);
        } else {
            CartItemsDal::updateCartItem($this->container, $cartItemObject);
        }
    }

    /**
     * Aim is to see if there is any existing discount rule for this SKU
     * If yes, retrieve the rule and set discount to true
     *
     * @throws \Exception
     */
    private function fetchPricingRules_bySku()
    {
        $rule_object = CartItemsDal::getPromoDiscountBySku($this->container, $this->sku);
        if (is_object($rule_object)) {
            //we have a discount
            $this->is_discounted = 1;
            $this->discount_list = $rule_object;
        }
    }

    /**
     * Idea behind is to see if there is any same item already in the cart
     * If yes, fetch it and update the new total qty for that item
     *
     * @throws \Exception
     */
    private function fetchSameItemInThisCart_bySku()
    {
        $existing_item_by_sku = CartItemsDal::getProductCountBySku($this->container, $this->sku, $this->cart_id);
        if (is_object($existing_item_by_sku)) {
            $this->similar_item_in_cart = $existing_item_by_sku;
            $this->do_insert            = false;
        }
    }

    /**
     * Will check if the current item has any corresponding Pricing Promo
     * If yes, the promo will be applied accordingly
     *
     * @param $qty_entered - this is the quantity that the sales person will enter
     */
    public function doApplicablePricingRule($qty_entered)
    {
        if ($this->is_discounted) {
            $product_occurrence = $this->discount_list->product_occurrence;

            if ($qty_entered >= $product_occurrence) {
                $promoCount                 = floor( ($qty_entered / $product_occurrence) );
                //NOTE: I could used MODULO as well for the below, i.e ($qty_entered % $product_occurrence) instead of ($product_occurrence * $promoCount)
                $remainingNormalPriceCount  = ($qty_entered - ($product_occurrence * $promoCount));
                $this->total_price          = ($promoCount * $this->discount_list->promo_price) + ($remainingNormalPriceCount * $this->unit_price);
                return;
            }
        }
        $this->total_price = ($qty_entered * $this->unit_price);
        return;
    }
}
