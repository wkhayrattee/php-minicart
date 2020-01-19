<?php
/**
 * Controller for handling Products
 *
 * @author Khayrattee Wasseem <wasseem@khayrattee.com>
 * @copyright Copyright (c) 2020 Wasseem Khayrattee
 * @license GPL-3.0
 * @link https://7php.com (website)
 */
namespace Controller;

use Model\ProductsDal;
use Project\Enum;
use Symfony\Component\HttpFoundation\Response;
use Wak\Common\ConstantList;
use Wak\Common\FilterClass;
use Wak\Common\ScriptHandler;
use Wak\Common\Utility;
use Wak\Common\Validator;

/**
 * Class ProductsAction
 * @package Controller
 */
class ProductsAction extends AbstractAction
{
    public $product_sku;
    public $product_name;
    public $product_price;

    /**
     * The main Action of this Class, get triggered FIRST
     *
     * @return mixed|Response
     * @throws \Exception
     */
    public function __invoke()
    {
        $this->pageAccessibleTo($this->container, Enum::P_ADMIN);
        $this->initPageMeta();
        $this->initPageScripts();

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
        $this->tpl->title       = 'Create Products | The Wak MiniCart';
        $this->tpl->description = 'This is for handling new Products creation';
    }

    /**
     * Handles the POST data
     * Validates the data & Sanitize
     * Finally Save in DB
     *
     * @return bool
     * @throws \Exception
     */
    private function handlePOST()
    {
        $result = true;
        if ($this->request->isMethod('POST')) {
            if (Validator::notEmptyOrNull($this->request->request->get('btn_create'))) {
                $result = $this->validateCreationAction();
            }

            if ($result === false) {
                $this->setMessageDiv(ConstantList::ENUM_DISPLAY_INLINE, ConstantList::ENUM_STATE_DIV_ERROR);
            } else {
                $product_object                 = new \stdClass();
                $product_object->sku            = $this->product_sku;
                $product_object->prod_name      = $this->product_name;
                $product_object->price          = $this->product_price;
                $product_object->date_created   = Utility::datenow();

                //No need to catch error, as it will be handled by app.php
                $result = ProductsDal::insertProduct($this->container, $product_object);
                unset($product_object);

                //if we got thus far, it's succcess
                $this->errorList[] = "Success! Product has been created";
                $this->setMessageDiv(ConstantList::ENUM_DISPLAY_INLINE, ConstantList::ENUM_STATE_DIV_SUCCESS);
            }
        }
        return $result;
    }

    private function validateCreationAction()
    {
        $this->errorList     = [];
        $this->product_sku   = trim($this->request->request->get('sku'));
        $this->product_name  = trim($this->request->request->get('name'));
        $this->product_price = trim($this->request->request->get('price'));

        //Validate SKU
        if (Validator::isEmptyOrNull($this->product_sku))  {
            $this->errorList[] = 'Please enter a value for SKU';
        } else if (ProductsDal::findProductBySKU($this->container, $this->product_sku) === true) {
            $this->errorList[] = 'This SKU is already present, choose another one!';
        }
        $this->product_sku = $this->tpl->product_sku = FilterClass::doSanitizeTitle($this->product_sku);
        if (strlen($this->product_sku) > 40) {
            $this->product_sku = substr($this->product_sku, 0, 40);
        }

        //Product Name
        if (Validator::isEmptyOrNull($this->product_name))  {
            $this->errorList[] = 'Please enter a product name';
        }
        $this->product_name = $this->tpl->product_name = FilterClass::doSanitizeTitle($this->product_name);
        if (strlen($this->product_name) > 255) {
            $this->product_name = substr($this->product_name, 0, 255);
        }

        //Validate price
        if (Validator::isEmptyOrNull($this->product_price))  {
            $this->errorList[] = 'Please enter price per unit';
        } elseif (! Validator::isNumeric($this->product_price) || $this->product_price <= 0) {
            $this->errorList[] = 'Price should be numeric';
        }
        $this->product_price = $this->tpl->product_price = (float)$this->product_price;

        //Finally return false is there's any error
        if (Validator::isEmptyOrNull($this->errorList)) {
            return true;
        }
        return false;
    }
}
