<?php
/**
 * Contains all constant definitions for use inside controllers, contained within the Container
 *
 * Why not just use the constant defined directly, is because I want inside the Controllers, we don't use magics, direct constant define using define() appears as magic.
 *
 * @author Khayrattee Wasseem <wasseem@khayrattee.com>
 * @copyright Copyright (c) 2020 Wasseem Khayrattee
 * @license GPL-3.0
 * @link https://7php.com (website)
 */
return [
    /// Site Specific
    'send_email_when_error' => FALSE,

    ///Domain
    'site.domain'           => (defined('SITE_DOMAIN') ? SITE_DOMAIN : ''),
    'site.name'             => (defined('SITE_NAME') ? SITE_NAME : ''),
    'site.secretkey'        => (defined('SECRET_KEY') ? SECRET_KEY : ''),
    'site.theme'            => (defined('THEME_NAME') ? THEME_NAME : ''),

    'pagination.size'       => 3,

    ///Folder path
    'folder.config'         => (defined('CONFIG_FOLDER') ? CONFIG_FOLDER : ''),
    'folder.src'            => (defined('SRC_FOLDER') ? SRC_FOLDER : ''),
    'folder.root'           => (defined('ROOT_FOLDER') ? ROOT_FOLDER : ''),
    'folder.public'         => (defined('WWW_FOLDER') ? WWW_FOLDER : ''),
    'folder.mvc'            => (defined('MVC_FOLDER') ? MVC_FOLDER : ''),
    'folder.view'           => (defined('TPL_FOLDER') ? TPL_FOLDER : ''),
    'folder.theme'          => (defined('THEME_FOLDER') ? THEME_FOLDER : ''),
    'folder.view.emails'    => (defined('TPL_FOLDER') ? TPL_FOLDER : '') . 'Emails' . DS,
    'folder.cache'          => (defined('CACHE_FOLDER') ? CACHE_FOLDER : ''),
    'folder.error.log'      => (defined('ERROR_LOG_FOLDER') ? ERROR_LOG_FOLDER : ''),

    ///Database Settings
    'db.host'               => 'localhost',
    'db.name'               => 'minicart',
    'db.username'           => 'minicart',
    'db.password'           => 'WUXj8dMatMQgZsvsU%v@*JbPBwUZwqM2Dr9Xnxi2GafHwta9X',
];
