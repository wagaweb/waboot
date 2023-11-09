<?php

namespace Waboot\inc\core\alert;

use Waboot\inc\core\alert\dispatcher\EmailAlertDispatcher;
use Waboot\inc\core\alert\dispatcher\FileAlertDispatcher;
use Waboot\inc\core\alert\dispatcher\SentryAlertDispatcher;

class AlertDispatcherFactory
{
    /**
     * @param string $name
     * @param string $dispatchTo
     * @param string|null $tz
     * @return EmailAlertDispatcher
     */
    public static function createEmailDispatcher(string $name, string $dispatchTo, string $tz = null): EmailAlertDispatcher
    {
        return new EmailAlertDispatcher($name,$dispatchTo,$tz);
    }

    /**
     * @param string $name
     * @param string $destFilePath
     * @param string|null $tz
     * @return FileAlertDispatcher
     */
    public static function createFileDispatcher(string $name, string $destFilePath, string $tz = null): FileAlertDispatcher
    {
        return new FileAlertDispatcher($name,$destFilePath,$tz);
    }

    /**
     * @param string $name
     * @param array $sentryArgs
     * @param string|null $tz
     * @return SentryAlertDispatcher
     */
    public function createSentryDispatcher(string $name, array $sentryArgs, string $tz = null): SentryAlertDispatcher
    {
        return new SentryAlertDispatcher($name,$sentryArgs,$tz);
    }
}