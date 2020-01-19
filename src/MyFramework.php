<?php
/**
 * The Application class of our system
 *
 * @author Khayrattee Wasseem <wasseem@khayrattee.com>
 * @copyright Copyright (c) 2020 Wasseem Khayrattee
 * @license GPL-3.0
 * @link https://7php.com (website)
 */
namespace Wak;

use Pimple\Container;
use SavantPHP\SavantPHP;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use FastRoute\Dispatcher;
use Wak\Common\Utility;

/**
 * Class MyFramework
 * @package Wak
 */
class MyFramework implements HttpKernelInterface
{
    /**
     * @var Container
     */
    private $container;
    /**
     * @var SavantPHP
     */
    private $tpl;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->tpl = $container['tpl'];
    }

    /**
     * @param Request $request
     * @param int $type
     * @param bool $catch
     * @return Response
     */
    public function handle(Request $request, int $type = HttpKernelInterface::MASTER_REQUEST, bool $catch = true) //REF: http://fabien.potencier.org/article/59/create-your-own-framework-on-top-of-the-symfony2-components-part-10
    {
        $response   = new Response();
        $dispatcher = $this->container['routes'];
        $routeInfo  = $dispatcher->dispatch($request->getMethod(), Utility::strtolower($request->getPathInfo()));
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                $response = new Response('404 Not Found', 404);
                break;

            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                $response       = new Response('405 Method Not Allowed', 405);
                break;

            case Dispatcher::FOUND:
                $handler    = $routeInfo[1];
                $vars       = $routeInfo[2];
                $object     = new $handler[0]($this->container, $vars);
                $methodName = $handler[1];
                $this->initTemplateVars($methodName);
                $response = $object(); // using Action Classes instead of Controller Classes, ref => http://pmjones.io/adr/
                /* seems slightly more time consuming than __invoke */
//                $response = call_user_func_array([$object, $methodName], $vars);
                break;

            default:
                $response = new Response('Something unexpected happen', 502);
        }
        return $response;
    }

    /**
     * @param $templateName
     */
    private function initTemplateVars($templateName)
    {
        $this->tpl->setTemplate('front/' . trim($templateName) . '.tpl.php');
    }
}
