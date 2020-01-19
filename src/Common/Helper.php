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
 * Class Helper
 * @package Wak\Common
 */
class Helper 
{
    /**
     * @param $error_msg_string
     * @param bool $isForWeb
     */
    public static function writeToLog($error_msg_string, $filenameForThisLog='errorlog', $isForWeb=true)
    {
        if($isForWeb === true) {
            Utility::WriteMsgToFile(ERROR_LOG_FOLDER . $filenameForThisLog . '.log', Utility::BuildErrorMessageForWeb($error_msg_string));
        } else {
            Utility::WriteMsgToFile(ERROR_LOG_FOLDER . $filenameForThisLog . '.log', Utility::BuildErrorMessage($error_msg_string));
        }
    }

    /**
     * @param $file_path
     * @return string
     */
    public static function js_inline($file_path)
    {
        return "\n\t".'<script type="text/javascript" src="' . $file_path . '"></script>'; //."\n"
    }

    /**
     * @param $file_path
     * @return string
     */
    public static function css_inline($file_path)
    {
        return "\n\t".'<link href="' . $file_path . '" rel="stylesheet" type="text/css" media="screen, projection"/>';//."\n"
    }

    /**
     * @param $container
     * @param $error
     * @param $fileName
     */
    public static function logErrorAndSendEmail(Container $container, \Exception $error, $fileName)
    {
        $errorString = "\r\n" . '## |' . date('Y-m-d H:i:s') . '| '. $error->getMessage() . ' in ' . $error->getFile() . "\r\n" . 'STACK TRACE: ' . $error->getTraceAsString();
        Helper::writeToLog($errorString, $fileName);

//        if ((bool) $container['config']['send_email_when_error'] === true) {
//            Helper::emailAdminAboutErrorOccurred($error, $container);
//        }
    }
}
