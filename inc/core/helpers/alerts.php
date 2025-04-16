<?php

namespace Waboot\inc\core\helpers;

use Waboot\inc\core\facades\Alerts;

/**
 * @param string|\Exception|\Throwable $e
 * @param string $source
 * @param string|null $url
 * @return void
 */
function dispatchGoogleChatAlert(string|\Exception|\Throwable $e, string $source = '', string $url = null): void {
    if(!$url){
        if(defined('GOOGLE_CHAT_ALERT_WEBHOOK')){
            $url = GOOGLE_CHAT_ALERT_WEBHOOK;
        }
    }
    $message = $e instanceof \Exception || $e instanceof \Throwable ? $e->getMessage() : $e;
    if(!empty($source)){
        $message = $source . ': ' . $message;
    }
    Alerts::dispatchGoogleChatAlert($message, $url);
    if($e instanceof \Exception || $e instanceof \Throwable){
        Alerts::dispatchGoogleChatAlert($e->getTraceAsString(), $url);
    }
}