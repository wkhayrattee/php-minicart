<?php
/**
 * To view invoicing
 *
 * @author Khayrattee Wasseem <wasseem@khayrattee.com>
 * @copyright Copyright (c) 2020 Wasseem Khayrattee
 * @license GPL-3.0
 * @link https://7php.com (website)
 */

namespace Controller;


use Model\CartDal;
use Project\Enum;
use Wak\Common\ScriptHandler;

class InvoiceAction extends AbstractAction
{
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

        $this->fetchInvoiceItemList();

        $this->response->setContent($this->tpl->getOutput());//->setTtl(3600);
        return $this->response;
    }

    private function initPageScripts()
    {
        /** @var ScriptHandler $scriptHandler */
        $scriptHandler = $this->container['scriptHandler'];
        $scriptHandler->addCss('bootstrap.min.css');
        $scriptHandler->addScript('bootstrap.min.js');
    }

    private function initPageMeta()
    {
        $this->tpl->title       = 'Invoice Page | The Wak MiniCart';
        $this->tpl->description = 'View invoicing';
    }

    private function fetchInvoiceItemList()
    {
        $cart_id = $this->urlParametersArray['cart_id'];
        if (isset($cart_id) && _isValidUuid($cart_id)) {
            $this->item_list = $this->tpl->itemList = CartDal::fetchInvoiceItemsByCart_id($this->container, $cart_id);
        }
        else { //the user is trying to access an invalid invoice
            redirect(path('/'));
        }
    }
}
