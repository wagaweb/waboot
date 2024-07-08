<?php

namespace Waboot\inc\core\facades;

use Waboot\inc\core\alert\AlertDispatcher;
use Waboot\inc\core\alert\Alert;
use Waboot\inc\core\alert\AlertDispatcherException;
use Waboot\inc\core\alert\AlertException;

class Alerts
{
	static function dispatchEmailAlert(string $title, string $message, string $recipient, \DateTimeZone $tz = null){
		try {
			$ad = new AlertDispatcher('export-rma-ad',AlertDispatcher::DISPATCH_METHOD_EMAIL,$recipient);
			$id = base64_encode($title.$message.$recipient);
			$ad->addAlert(new Alert($id,$title,$message,$tz));
			$ad->dispatch();
		} catch (AlertException|AlertDispatcherException $e) {
			error_log('Alerts::dispatchEmailAlert ERROR: '.$e->getMessage());
		}
	}
}