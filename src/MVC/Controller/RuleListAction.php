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
use Wak\Common\PaginationMaths;
use Wak\Common\ScriptHandler;

class RuleListAction extends AbstractAction
{

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

        $this->showRuleList();

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
        $scriptHandler->addCss('jquery.dataTables.min.css');
        $scriptHandler->addScript('jquery-2.2.4.min.js');
        $scriptHandler->addScript('bootstrap.min.js');
        $scriptHandler->addScript('jquery.dataTables.js');
        $scriptHandler->addScript('initDatatable.js');
    }

    /**
     * Set the PAGE meta data
     */
    private function initPageMeta()
    {
        $this->tpl->title       = 'View Rule List | The Wak MiniCart';
        $this->tpl->description = 'This is the pricing rule list page';
    }

    /**
     * Handle displaying of all products in system
     * Caters for Pagination on a per page size basis for performance
     *
     * @throws \Exception
     */
    private function showRuleList()
    {
        $this->tpl->pageCount   = 0;
        $current_page_number    = 1;
        $page_display_size      = isset($this->container['config']['pagination.size']) ? $this->container['config']['pagination.size'] : 10;

        if (isset($this->urlParametersArray['page'])) {
            $current_page_number = (int)$this->urlParametersArray['page'];
        }
        $record_count = CartItemsDal::getRulesCount($this->container);
        if ($record_count == -1) {
            $this->tpl->ruleList = null;
        } else {
            $paginator                  = new PaginationMaths($page_display_size, $record_count, $current_page_number);
            $this->tpl->pageCount       = $paginator->getMaximumPagesPossible();
            $this->tpl->currentPage     = $paginator->getCurrentWebPageNumber();
            $this->tpl->start_page      = $paginator->getRecordNumberForStartOfOffset();
            $this->tpl->last_page       = $paginator->getRecordNumberForEndOfOffset();
            $this->tpl->record_count    = $record_count;
            $this->tpl->ruleList        = ProductsDal::getRuleList($this->container, $paginator->getOffset(), $page_display_size);
        }
    }
}
