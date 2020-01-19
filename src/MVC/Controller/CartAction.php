<?php
/**
 * Controller for handling the cart and checkout
 *
 * @author Khayrattee Wasseem <wasseem@khayrattee.com>
 * @copyright Copyright (c) 2020 Wasseem Khayrattee
 * @license GPL-3.0
 * @link https://7php.com (website)
 */
namespace Controller;

use Model\CartDal;
use Model\CartItemsDal;
use Model\ProductsDal;
use Project\CartItemLogic;
use Project\Enum;
use Wak\Common\ConstantList;
use Wak\Common\FilterClass;
use Wak\Common\NativeSession;
use Wak\Common\ScriptHandler;
use Wak\Common\TokenClass;
use Wak\Common\Utility;
use Wak\Common\Validator;

class CartAction extends AbstractAction
{
    private $cart_id;
    private $checkout_status;
    private $sku_select;
    private $add_qty;
    private $product_list;
    private $item_list;
    /**
     * This is the heart of our controller ADR pattern (Action-Domain-Responder)
     *
     * @return mixed|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function __invoke()
    {
        $this->pageAccessibleTo($this->container, Enum::P_ADMIN);
        $this->initPageMeta();
        $this->initPageScripts();

        $this->checkout();
        $this->handleCartObject();
        $this->fetchProductList();
        $this->handleAddProduct();
        $this->fetchItemList();

        $this->response->setContent($this->tpl->getOutput());//->setTtl(3600);
        return $this->response;
    }

    /**
     * set page assets individually
     */
    private function initPageScripts()
    {
        /** @var ScriptHandler $scriptHandler */
        $scriptHandler = $this->container['scriptHandler'];
        $scriptHandler->addCss('bootstrap.min.css');
        $scriptHandler->addScript('bootstrap.min.js');
    }

    /**
     * Set the PAGE meta data
     */
    private function initPageMeta()
    {
        $this->tpl->title       = 'CART | The Wak MiniCart';
        $this->tpl->description = 'This is the CART page';
    }

    /**
     * Populate our select box with all products
     * TODO: To improve performance, in real life, we will use an AUTO-COMPLETE box + validating for whitelist
     *
     * @throws \Exception
     */
    private function fetchProductList()
    {
        $this->product_list = $this->tpl->productList = ProductsDal::fetchAllProduct($this->container);
    }

    /**
     * Fetch all cart items, for this specific cart_id
     *
     * @throws \Exception
     */
    private function fetchItemList()
    {
        $this->item_list = $this->tpl->itemList = CartItemsDal::fetchAllItemsByCartId($this->container, $this->cart_id);
    }

    private function checkout()
    {
        if ($this->request->isMethod('POST') && Validator::notEmptyOrNull($this->request->request->get('btn_checkout')) ) {
            /** @var NativeSession $sessionObj */
            $sessionObj     = $this->container['session'];
            $this->cart_id  = $sessionObj->read(ConstantList::KEY_SESSION_CART_ID);

            CartDal::updateCart($this->container, $this->cart_id);
            $this->clearCart();
            redirect(path('invoice') . $this->cart_id);
            exit;
        }
    }

    /**
     * Get unit price of a sku
     * @param $productList
     * @param $sku
     * @return mixed
     */
    private function getUnitPrice($productList, $sku)
    {
        $index_of_sku = in_multi_array($sku, $productList);
        return $productList[$index_of_sku]['price'];
    }

    /**
     * Clear all cart items
     */
    private function clearCart()
    {
        /** @var NativeSession $sessionObj */
        $sessionObj = $this->container['session'];
        $sessionObj->remove(ConstantList::KEY_SESSION_CART_ID);
        $sessionObj->remove(ConstantList::KEY_SESSION_CHECKOUT_STATUS);
        $sessionObj->destroy();
    }

    /**
     * Should create a new cart on page load, UNLESS:
     * If session exists, & cart has not been checkout, use that cart instead
     */
    private function handleCartObject()
    {
        //Clear Cart
        if ($this->request->isMethod('POST') && Validator::notEmptyOrNull($this->request->request->get('btn_clear')) ) {

            $this->clearCart();
            redirect(path('cart'));
            exit;
        }

        /** @var NativeSession $sessionObj */
        $sessionObj = $this->container['session'];
        if ($sessionObj->has(ConstantList::KEY_SESSION_CART_ID) && ((bool)$sessionObj->read(ConstantList::KEY_SESSION_CHECKOUT_STATUS) == 0)) {

            $this->cart_id = $sessionObj->read(ConstantList::KEY_SESSION_CART_ID);

        } else { //Need to create New Session & Cart Object in table cart
            $this->cart_id          = TokenClass::uuid();
            $this->checkout_status  = 0;

            $sessionObj->write(ConstantList::KEY_SESSION_CART_ID, $this->cart_id);
            $sessionObj->write(ConstantList::KEY_SESSION_CHECKOUT_STATUS, $this->checkout_status);

            //now create entry in DB
            //cart_id, checkout_status, date_created, total_price
            $cart_object                    = new \stdClass();
            $cart_object->cart_id           = $this->cart_id;
            $cart_object->checkout_status   = $this->checkout_status;
            $cart_object->date_created      = Utility::datenow();
            CartDal::insertCart($this->container, $cart_object);
        }
    }

    /**
     * Allows to add (scanning of) products in any order,
     * with any qty and can be added repeatedly
     *
     * @return bool|string
     * @throws \Exception
     */
    private function handleAddProduct()
    {
        $result = true;
        if ($this->request->isMethod('POST')) {
            if ( Validator::notEmptyOrNull($this->request->request->get('btn_add_product'))) {
                $result = $this->validateProductionAddition();
            }

            if ($result === false) {
                $this->setMessageDiv(ConstantList::ENUM_DISPLAY_INLINE, ConstantList::ENUM_STATE_DIV_ERROR);
            } else {
                $cart_item                         = new \stdClass();
                $cart_item->cart_id                = $this->cart_id;
                $cart_item->sku                    = $this->sku_select;
                $cart_item->prod_qty               = $this->add_qty;
                $cart_item->unit_price             = $this->getUnitPrice($this->product_list, $cart_item->sku);

                $cartLogic = new CartItemLogic($cart_item, $this->container);
                $cartLogic->addToCart();
                unset($cartLogic);

                //if we got thus far, it's succcess
                $this->errorList[] = "Success! Product has been added to cart";
                $this->setMessageDiv(ConstantList::ENUM_DISPLAY_INLINE, ConstantList::ENUM_STATE_DIV_SUCCESS);
            }
        }
        return $result;
    }

    private function validateProductionAddition()
    {
        $this->errorList    = [];
        $this->sku_select   = trim($this->request->request->get('sku_select'));
        $this->add_qty      = trim($this->request->request->get('add_qty'));

        //Validate SKU
        if (Validator::isEmptyOrNull($this->sku_select) || ($this->sku_select == -1) )  {
            $this->errorList[] = 'Please select a sku';
        }
        $this->sku_select = $this->tpl->sku_select = FilterClass::doSanitizeTitle($this->sku_select);

        //Validate Product Qty
        if (Validator::isEmptyOrNull($this->add_qty) || ($this->add_qty == 0))  {
            $this->errorList[] = 'Please enter quantity';
        } elseif (! Validator::isNumeric($this->add_qty) || $this->add_qty <= 0) {
            $this->errorList[] = 'Quantity should be numeric';
        }
        $this->add_qty = $this->tpl->add_qty = (int)$this->add_qty;

        //Finally return false is there's any error
        if (Validator::isEmptyOrNull($this->errorList)) {
            return true;
        }
        return false;
    }
}
