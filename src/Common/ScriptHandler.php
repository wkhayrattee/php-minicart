<?php
/**
 *
 * @author Khayrattee Wasseem <wasseem@khayrattee.com>
 * @copyright Copyright (c) 2020 Wasseem Khayrattee
 * @license GPL-3.0
 * @link https://7php.com (website)
 */
namespace Wak\Common;

use Pimple\Container;

/**
 * Class ScriptHandler
 * @package Wak\Common
 */
class ScriptHandler
{
    private $js_top_array = [];
    private $js_bottom_array = [];
    private $css_array = [];
    private $js_path;
    private $css_path;
    private $pathToPublicFolder;

    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->js_path = $container['config']['folder.public'] . 'theme/' . $container['config']['site.theme'] . 'assets/js/';
        $this->css_path = $container['config']['folder.public'] . 'theme/' . $container['config']['site.theme'] . '/assets/css/';
        $this->pathToPublicFolder = $this->container['config']['folder.public'];
    }

    /**
     * @param $file_name
     * @param bool $isTop
     */
    public function addScript($file_name, $isTop=false)
    {
        if ($isTop) {
            $this->js_top_array[] = $file_name;
        }
        else {
            $this->js_bottom_array[] = $file_name;
        }
    }

    /**
     * @param $file_name
     */
    public function addCss($file_name)
    {
        $this->css_array[] = $file_name;
    }

    /**
     * @param bool $isTop
     * @return string
     */
    public function getScriptString($isTop=false)
    {
        if ($isTop) {
            $js_array = $this->js_top_array;
        }
        else {
            $js_array = $this->js_bottom_array;
        }

        $tmp_string = '';
        if (Validator::notEmptyOrNull($js_array)) {
            foreach ($js_array as $js_file) {
                if (file_exists($this->pathToPublicFolder . $this->js_path . $js_file) ) {
                    $tmp_string .= Helper::js_inline($this->js_path . $js_file);

                } else if (file_exists($this->pathToPublicFolder . $this->js_path . $js_file) ) {
                    $tmp_string .= Helper::js_inline($this->js_path . $js_file);

                } else {
                    $msg = "Cannot find the js file at: " . $this->pathToPublicFolder . $this->js_path . $js_file;
                    Helper::logErrorAndSendEmail($this->container, new \Exception($msg), 'ScriptHandler');
                }
            }
        }
        return $tmp_string;
    }

    /**
     * @return string
     */
    public function getCssString()
    {
        $tmp_string = '';
        if(Validator::notEmptyOrNull($this->css_array)) {
            foreach($this->css_array as $css_file) {
                if (!file_exists($this->pathToPublicFolder . $this->css_path . $css_file)) {
                    $msg = "Cannot find the css file at: " . $this->pathToPublicFolder . $this->css_path . $css_file;
                    Helper::logErrorAndSendEmail($this->container, new \Exception($msg), 'ScriptHandler');

                } else {
                    $tmp_string .= Helper::css_inline($this->css_path . $css_file);
                }
            }
        }
        return $tmp_string;
    }

    /**
     * @param $js_path
     */
    public function set_js_path($js_path)
    {
        $this->js_path = $js_path;
    }

    /**
     * @param $css_path
     */
    public function set_css_path($css_path)
    {
        $this->css_path = $css_path;
    }
}
