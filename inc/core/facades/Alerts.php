<?php

namespace Waboot\inc\core\facades;

use Waboot\inc\core\alert\AlertDispatcher;
use Waboot\inc\core\alert\Alert;
use Waboot\inc\core\alert\AlertDispatcherException;
use Waboot\inc\core\alert\AlertException;
use Waboot\inc\core\alert\dispatcher\GoogleChatDispatcher;
use function Waboot\inc\core\helpers\logException;

class Alerts
{
    static function dispatchEmailAlert(string $title, string $message, string $recipient, \DateTimeZone $tz = null){
        try {
            $ad = new AlertDispatcher('ad',AlertDispatcher::DISPATCH_METHOD_EMAIL,$recipient);
            $id = base64_encode($title.$message.$recipient);
            $ad->addAlert(new Alert($id,$title,$message,$tz));
            $ad->dispatch();
        } catch (AlertException|AlertDispatcherException $e) {
            error_log('Alerts::dispatchEmailAlert ERROR: '.$e->getMessage());
        }
    }

    static function dispatchGoogleChatAlert(string $message, string $url, \DateTimeZone $tz = null): void
    {
        try {
            if(!class_exists('Waboot\inc\core\alert\dispatcher\GoogleChatDispatcher')){
                require_once get_stylesheet_directory() . '/inc/core/helpers/alert/dispatcher/GoogleChatDispatcher.php';
            }
            $ad = new GoogleChatDispatcher('gd',$url, AlertDispatcher::DISPATCH_METHOD_EMAIL);
            $id = base64_encode($message);
            $ad->addAlert(new Alert($id,'',$message,$tz));
            $ad->dispatch();
        } catch (AlertException|AlertDispatcherException $e) {
            logException($e,'Alerts::dispatchGoogleChatAlert');
        }
    }
}