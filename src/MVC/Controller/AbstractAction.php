<?php
/**
 * Define a Contract for all controllers to adhere to.
 *
 * @author Khayrattee Wasseem <wasseem@khayrattee.com>
 * @copyright Copyright (c) 2020 Wasseem Khayrattee
 * @license GPL-3.0
 * @link https://7php.com (website)
 */
namespace Controller;

use Pimple\Container;
use Project\Enum;
use SavantPHP\SavantPHP;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wak\Common\ConstantList;

/**
 * Class AbstractAction
 * @package Controller
 */
abstract class AbstractAction
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var SavantPHP
     */
    protected $tpl;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var Request
     */
    protected $request;

    protected $urlParametersArray;

    /**
     * @var array
     */
    protected $errorList;

    /**
     * Any child class will be forced to implement this method
     * This is the heart of our ADR pattern (Action-Domain-Responder)
     *
     * @return mixed
     */
    abstract public function __invoke();


    /**
     * AbstractAction constructor.
     * @param Container $container
     * @param $urlParametersArray
     */
    public function __construct(Container $container, $urlParametersArray)
    {
        $this->errorList = [];
        $this->urlParametersArray = $urlParametersArray;
        $this->tpl          = $container['tpl'];
        $this->container    = $container;
        $this->request      = $container['request'];
        $this->response     = new Response();

        $this->initSomeVars();
    }

    protected function initSomeVars()
    {
        $this->tpl->themeversion = $this->container['config']['site.theme'];
        $this->setMessageDiv();
    }

    /**
     * @param string $visibility
     * @param string $css_class
     */
    protected function setMessageDiv($visibility=ConstantList::ENUM_DISPLAY_NONE, $css_class=ConstantList::ENUM_STATE_DIV_INFO)
    {
        $this->tpl->message_div_visibility  = $visibility;
        $this->tpl->message_state_css_class = $css_class;
        $this->tpl->errorList               = $this->errorList;
    }

    /**
     * @param Container $container
     * @param $entity_type_enum
     * @return bool
     */
    protected function pageAccessibleTo(Container $container, $entity_type_enum)
    {
        $result = false;
        switch($entity_type_enum) {
            case Enum::P_ADMIN:
                //normally should handle page accessible only to ADMINs
                $result = true;
                break;

            case Enum::P_GUEST:
                //should normally redirect to home page, as guest are not allowed
                $result = false;
                break;

            default:
                $result = false;
        }
        return $result;
    }
}
