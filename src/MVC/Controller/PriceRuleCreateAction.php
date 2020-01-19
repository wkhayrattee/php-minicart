<?php
/**
 * @author Khayrattee Wasseem <wasseem@khayrattee.com>
 * @copyright Copyright (c) 2020 Wasseem Khayrattee
 * @license GPL-3.0
 * @link https://7php.com (website)
 */

namespace Controller;


use Model\CartItemsDal;
use Model\ProductsDal;
use Project\Enum;
use Wak\Common\ConstantList;
use Wak\Common\FilterClass;
use Wak\Common\Utility;
use Wak\Common\Validator;

class PriceRuleCreateAction extends AbstractAction
{
    private $sku_select;
    private $prod_count;
    private $prod_promo;

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

        $this->fetchProductList();
        $this->handlePOST();

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
        $this->tpl->title       = 'Create Pricing rules | The Wak MiniCart';
        $this->tpl->description = 'This is the Pricing rule creation page';
    }

    /**
     * Populate our select box with all products
     * TODO: To improve performance, in real life, we will use an AUTO-COMPLETE box + validating for whitelist
     *
     * @throws \Exception
     */
    private function fetchProductList()
    {
        $this->productList = $this->tpl->productList = ProductsDal::fetchAllProduct($this->container);
    }

    private function handlePOST()
    {
        $result = true;
        if ($this->request->isMethod('POST')) {
            if ( Validator::notEmptyOrNull($this->request->request->get('btn_create'))) {
                $result = $this->validateCreationAction();
            }

            if ($result === false) {
                $this->setMessageDiv(ConstantList::ENUM_DISPLAY_INLINE, ConstantList::ENUM_STATE_DIV_ERROR);
            } else {
                $pricing_object                         = new \stdClass();
                $pricing_object->sku                    = $this->sku_select;
                $pricing_object->product_occurrence     = $this->prod_count;
                $pricing_object->promo_price            = $this->prod_promo;
                $pricing_object->date_created           = Utility::datenow();

                //No need to catch error, as it will be handled by app.php
                $result = ProductsDal::insertNewRule($this->container, $pricing_object);
                unset($pricing_object);

                //if we got thus far, it's succcess
                $this->errorList[] = "Success! Rule has been created";
                $this->setMessageDiv(ConstantList::ENUM_DISPLAY_INLINE, ConstantList::ENUM_STATE_DIV_SUCCESS);
            }
        }
        return $result;
    }

    private function validateCreationAction()
    {
        $this->errorList    = [];
        $this->sku_select   = trim($this->request->request->get('sku_select'));
        $this->prod_count   = trim($this->request->request->get('prod_count'));
        $this->prod_promo   = trim($this->request->request->get('prod_promo'));

        //Validate SKU
        if (Validator::isEmptyOrNull($this->sku_select) || ($this->sku_select == -1) )  {
            $this->errorList[] = 'Please select a sku';
        } else {
            $discount_object = CartItemsDal::getPromoDiscountBySku($this->container, $this->sku_select);
            if (is_object($discount_object)) {
                $this->errorList[] = 'There is already a rule for this SKU!';
            }
        }
        $this->sku_select = $this->tpl->sku_select = FilterClass::doSanitizeTitle($this->sku_select);

        //Validate promo price
        if (Validator::isEmptyOrNull($this->prod_promo) || ($this->prod_promo == 0))  {
            $this->errorList[] = 'Please enter promo price';
        } elseif (! Validator::isNumeric($this->prod_promo) || $this->prod_promo <= 0) {
            $this->errorList[] = 'Promo Price should be numeric';
        }
        $this->prod_promo = $this->tpl->prod_promo = (float)$this->prod_promo;

        //Validate prod_count
        if (Validator::isEmptyOrNull($this->prod_count) || ($this->prod_count == 0))  {
            $this->errorList[] = 'Please enter Occurrence';
        } elseif (! Validator::isNumeric($this->prod_count) || $this->prod_count <= 0) {
            $this->errorList[] = 'Occurrence should be numeric';
        }
        $this->prod_count = $this->tpl->prod_count = (float)$this->prod_count;

        //Finally return false is there's any error
        if (Validator::isEmptyOrNull($this->errorList)) {
            return true;
        }
        return false;
    }
}
