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
            $tzName = $tz !== null ? $tz->getName() : null;
            $ad = new AlertDispatcher('ad',AlertDispatcher::DISPATCH_METHOD_EMAIL,$recipient);
            $id = base64_encode($title.$message.$recipient);
            $ad->addAlert(new Alert($id,$title,$message,$tzName));
            $ad->dispatch();
        } catch (AlertException|AlertDispatcherException $e) {
            error_log('Alerts::dispatchEmailAlert ERROR: '.$e->getMessage());
        }
    }

    /**
     * @see: https://developers.google.com/workspace/chat/quickstart/webhooks
     * @param string $message
     * @param string $url
     * @param \DateTimeZone|null $tz
     * @return void
     */
    static function dispatchGoogleChatAlert(string $message, string $url, \DateTimeZone $tz = null): void
    {
        try {
            $tzName = $tz !== null ? $tz->getName() : null;
            $ad = new GoogleChatDispatcher('gd',$url,$tzName);
            $id = base64_encode($message);
            $ad->addAlert(new Alert($id,'',$message,$tzName));
            $ad->dispatch();
        } catch (AlertException|AlertDispatcherException $e) {
            logException($e,'Alerts::dispatchGoogleChatAlert');
        }
    }
}