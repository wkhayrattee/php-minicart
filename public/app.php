<?php
/**
 * The main entry point of the Public folder - a FRONT 'controller'
 *
 * @author Khayrattee Wasseem <wasseem@khayrattee.com>
 * @copyright Copyright (c) 2020 Wasseem Khayrattee
 * @license GPL-3.0
 * @link https://7php.com (website)
 */
require_once '../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpCache\Store;
use Symfony\Component\HttpKernel\HttpCache\HttpCache;
use Wak\MyFramework;

if (! (defined('PHP_VERSION_ID') && (PHP_VERSION_ID >= 70205))) {
    die('wrong PHP version, needs to be higher than v7.2.5');
} else if (! (defined('PHP_VERSION') && (PHP_VERSION_ID >= 70205))) {
    die('wrong PHP version, needs to be higher than v7.2.5');
}

date_default_timezone_set('UTC');

register_shutdown_function('fatalErrorShutdownHandler');
function fatalErrorShutdownHandler()
{
    $last_error = error_get_last();
    if ($last_error['type'] === E_ERROR) {
        // fatal error
        $string_to_save     = "\r\n" . '## |' . date('Y-m-d H:i:s') . '| '.$last_error['message'] . ' | ' . $last_error['file'] . ' | ' . $last_error['line'];
        handle_error_content($string_to_save);
    }
}
set_exception_handler("myExceptionHandler");
function myExceptionHandler($error)
{
    $string_to_save     = "\r\n" . '## |' . date('Y-m-d H:i:s') . '| '. $error->getMessage() . ' in ' . $error->getFile() . "\r\n" . 'STACK TRACE: ' . p($error, false);
    handle_error_content($string_to_save);
}
set_error_handler("myErrorHandler", E_ALL);
function myErrorHandler($errno, $errstr)
{
    throw new \Exception($errstr, $errno);
}

/**
 * Save error content in app_errorlog and display error.html
 *
 * @param $error_content_to_save
 */
function handle_error_content($error_content_to_save)
{
    $fullPathToFileName = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'app_error.log';
    $handle = @fopen($fullPathToFileName, 'a+');
    if ($handle === false) {
        //silence is golden
    } else {
        @fwrite($handle, $error_content_to_save . "\r\n" . "\r\n");
        fclose($handle);
    }
    ob_end_clean();
    echo file_get_contents(__DIR__ . '/error.html');
}


try {
    include_once '../config/functions.php';

    define('THEME_NAME', 'v1'); //would be our default theme

    if(!defined('DS')) {
        define('DS', DIRECTORY_SEPARATOR); //[NOTE: do not confuse with PATH_SEPARATOR == : ]
    }
    define('ROOT_FOLDER',       dirname(__DIR__) . DS);
    define('WWW_FOLDER',        ROOT_FOLDER . 'public' . DS);
    define('SESSION_FOLDER',    ROOT_FOLDER . 'sessions' . DS);
    define('CACHE_FOLDER',      ROOT_FOLDER . 'cache' . DS);
    define('CONFIG_FOLDER',     ROOT_FOLDER . 'config' . DS);
    define('SRC_FOLDER',        ROOT_FOLDER . 'src' . DS);
    define('MVC_FOLDER',        SRC_FOLDER . 'MVC' . DS);
    define('TPL_FOLDER',        MVC_FOLDER . 'View' . DS);
    define('ERROR_LOG_FOLDER',  ROOT_FOLDER . 'log' . DS);
    define('THEME_FOLDER',      TPL_FOLDER . THEME_NAME . DS);

    define('SITE_NAME', 'Wak MiniCart');
    define('SECRET_KEY', '7e0Z005Lb3c6f0r6_xxx_7e0Z005Lb3c6f0r6');
    // </editor-fold>

    if (!is_writable(SESSION_FOLDER)) {
        throw new Exception('Cannot access the Sessions folder at: ' . SESSION_FOLDER . ' - most probably a write permission issue as that folder should be there?');
    }

    // <editor-fold desc="site modes: DEV or LIVE">
    switch (true) {
        case strstr($_SERVER['HTTP_HOST'], ".local"):
            define('SITE_MODE', 'DEV');
            define('SITE_DOMAIN', $_SERVER['SERVER_NAME']);
            define('IS_DEBUG_ENABLED', true);
            break;
        default:
            define('SITE_MODE', 'LIVE');
            define('SITE_DOMAIN', $_SERVER['SERVER_NAME']);
            define('IS_DEBUG_ENABLED', false);
            break;
    }
    // </editor-fold>

    if (SITE_MODE != 'LIVE') {
        error_reporting(E_ALL);
        ini_set('display_errors', 'On');
    } else {
        //empty for now
    }

    $routes                     = include_once '../config/routes_cached.php';
    $appContainer               = new Pimple\Container(['config' => include_once '../config/defines.php']);
    $appContainer['routes']     = $routes;
    $appContainer['request']    = $request = Request::createFromGlobals();

    //Register some services on our Pimple Container | Put in another file to prevent too much overwhelming code in here
    include_once '../config/register_services.php';

    $app        = new MyFramework($appContainer);
    $appCached  = new HttpCache($app, new Store(CACHE_FOLDER . 'httpcache'));
    $response   = $appCached->handle($request)->send();
    $appCached->terminate($request, $response);

} catch (Exception $error) {
    $string_to_save     = "\r\n" . '## |' . date('Y-m-d H:i:s') . '| '. $error->getMessage() . ' in ' . $error->getFile() . "\r\n" . 'STACK TRACE: ' . p($error, false);
    handle_error_content($string_to_save);
}
