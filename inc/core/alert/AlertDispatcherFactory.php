<?php

namespace Waboot\inc\core\alert;

use Waboot\inc\core\alert\dispatcher\EmailAlertDispatcher;
use Waboot\inc\core\alert\dispatcher\FileAlertDispatcher;

class AlertDispatcherFactory
{
    /**
     * @param string $name
     * @param string $dispatchTo
     * @param \DateTimeZone|null $tz
     * @return EmailAlertDispatcher
     */
    public static function createEmailDispatcher(string $name, string $dispatchTo, ?\DateTimeZone $tz = null): EmailAlertDispatcher
    {
        return new EmailAlertDispatcher($name,$dispatchTo,$tz);
    }

    /**
     * @param string $name
     * @param string $destFilePath
     * @param \DateTimeZone|null $tz
     * @return FileAlertDispatcher
     */
    public static function createFileDispatcher(string $name, string $destFilePath, ?\DateTimeZone $tz = null): FileAlertDispatcher
    {
        return new FileAlertDispatcher($name,$destFilePath,$tz);
    }
}