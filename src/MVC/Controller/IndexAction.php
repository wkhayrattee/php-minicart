<?php
/**
 * Controller for handling root index & welcome
 *
 * @author Khayrattee Wasseem <wasseem@khayrattee.com>
 * @copyright Copyright (c) 2020 Wasseem Khayrattee
 * @license GPL-3.0
 * @link https://7php.com (website)
 */
namespace Controller;

use Project\Enum;
use Wak\Common\ScriptHandler;

/**
 * Class IndexAction
 * @package Controller
 */
class IndexAction extends AbstractAction
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

        $this->response->setContent($this->tpl->getOutput())->setTtl(3600);
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
        $this->tpl->title       = 'The Wak MiniCart';
        $this->tpl->description = 'This is the dashboard of this project';
    }
}
