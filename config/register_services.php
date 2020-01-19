<?php
/**
 * Register some services on our Pimple Container
 *
 * @author Khayrattee Wasseem <wasseem@khayrattee.com>
 * @copyright Copyright (c) 2020 Wasseem Khayrattee
 * @license GPL-3.0
 * @link https://7php.com (website)
 */

/* SavantPHP Instance */
$appContainer['tpl'] = function ($c) { // return same instance each time
    return new \SavantPHP\SavantPHP([
        \SavantPHP\SavantPHP::CONTAINER     => $c,
        \SavantPHP\SavantPHP::TPL_PATH_LIST => [
            $c['config']['folder.view'],
            $c['config']['folder.view'] . $c['config']['site.theme']
    ]]);
};

/* Database Connection Instance */
$appContainer['connection'] = $appContainer->factory(function ($c) { //I want a different instance each time, so using factory
    $connObject = new \Model\PdoConnection($c);
    return $connObject->getConnection();
});

/* Get a new password */
$appContainer['password.new'] = $appContainer->factory(function ($c) { //I want a different instance each time, so using factory
    $plainTextPassword  = \Wak\Common\TokenClass::generatePassword(10);
    $passwordObject     = new \Wak\Common\Password($plainTextPassword);
    $hashedPassword     = $passwordObject->getHash();
    return [$plainTextPassword => $hashedPassword];
});


/* Session Object */
$appContainer['session'] = function ($c) { // return same instance each time
    /* as per REF => https://paragonie.com/blog/2015/04/fast-track-safe-and-secure-php-sessions */
    ini_set('session.save_path', (defined('SESSION_FOLDER') ? SESSION_FOLDER : '')); //TODO: Enable this on LIVE and at home
    ini_set('session.save_handler', 'files');
    ini_set('session.use_cookies', 1);
    ini_set('session.cookie_domain', (defined('SITE_DOMAIN') ? SITE_DOMAIN : ''));
    ini_set('session.entropy_length', 32);
    ini_set('session.entropy_file', '/dev/urandom');
    ini_set('session.hash_function', 'sha256');
    ini_set('session.hash_bits_per_character', 5);
    /*
     * to make sure that PHP only uses cookies for sessions and disallow session ID passing as a GET parameter
     */
    ini_set('session.use_only_cookies', 1);
    /*
     * By specifying the HttpOnly flag when setting the session cookie you can tell a users browser not to expose the cookie to client side scripting such as JavaScript.
     * This makes it harder for an attacker to hijack the session ID and masquerade as the effected user.
     */
    ini_set('session.cookie_httponly', 1);
    /*
     * session.cookie_secure specifies whether cookies should only be sent over secure connections (HTTPS).
     * If you're using HTTP, you won't get any cookies from the server. That's why you don't have a session.
     * REF => http://stackoverflow.com/a/25047234
     */
//    ini_set('session.cookie_secure', 1);

    $sessionConfiguration = [
        'lifetime' => 14400, //4hrs  -- this should be in secs (1800 == 30mins)
        'domain'   => (defined('SITE_DOMAIN') ? SITE_DOMAIN : ''),
    ];
    $session = new \Wak\Common\NativeSession($sessionConfiguration);
    $session->start();
    return $session;
};


/* ScripHandler is used to output CSS or JS inside template files on the fly, dynamically */
$appContainer['scriptHandler'] = function ($c) { //NOTE: By default, each time you get a service, Pimple returns the same instance of it. If you want a different instance to be returned for all calls, wrap your anonymous function with the factory() method
    $scriptHandler = new \Wak\Common\ScriptHandler($c);
    $scriptHandler->set_js_path('/theme/' . $c['config']['site.theme'] . '/assets/js/');
    $scriptHandler->set_css_path('/theme/' . $c['config']['site.theme'] . '/assets/css/');
    return $scriptHandler;
};
