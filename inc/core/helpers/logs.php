<?php

namespace Waboot\inc\core\helpers;

/**
 * @param string $loggerIdentifier
 * @param string $logMessage
 * @param int $logLevel
 * @param array $context
 * @param \DateTimeZone|null $dz
 * @return void
 */
function logToFile(string $loggerIdentifier, string $logMessage, int $logLevel = MonologLoggingLevels::INFO, array $context = [], \DateTimeZone|null $dz = null): void {
    Waboot()->logToFile($loggerIdentifier,$logMessage,$logLevel,$context,$dz);
}

/**
 * @param string $loggerIdentifier
 * @param string $logMessage
 * @param array $context
 * @param \DateTimeZone|null $dz
 * @return void
 */
function logInfoToFile(string $loggerIdentifier, string $logMessage, array $context = [], \DateTimeZone|null $dz = null): void {
    Waboot()->logToFile($loggerIdentifier,$logMessage,MonologLoggingLevels::INFO,$context,$dz);
}

/**
 * @param string $loggerIdentifier
 * @param string $logMessage
 * @param array $context
 * @param \DateTimeZone|null $dz
 * @return void
 */
function logWarningToFile(string $loggerIdentifier, string $logMessage, array $context = [], \DateTimeZone|null $dz = null): void {
    Waboot()->logToFile($loggerIdentifier,$logMessage,MonologLoggingLevels::WARNING,$context,$dz);
}

/**
 * @param string $loggerIdentifier
 * @param string $logMessage
 * @param array $context
 * @param \DateTimeZone|null $dz
 * @return void
 */
function logErrorToFile(string $loggerIdentifier, string $logMessage, array $context = [], \DateTimeZone|null $dz = null): void {
    Waboot()->logToFile($loggerIdentifier,$logMessage,MonologLoggingLevels::ERROR,$context,$dz);
}

/**
 * Shorthand to log info into a 'waboot-log'
 *
 * @param string $message
 * @param string $source
 * @param array $context
 * @param string $fileName
 * @return void
 */
function logInfo(string $message, string $source, array $context = [], string $fileName = 'waboot-log'): void {
    if($source !== ''){
        $context['source'] = $source;
    }
    logInfoToFile($fileName,$message,$context);
}

/**
 * Shorthand to log warning into a 'waboot-log'
 *
 * @param string $message
 * @param string $source
 * @param array $context
 * @param string $fileName
 * @return void
 */
function logWarning(string $message, string $source, array $context = [], string $fileName = 'waboot-log'): void {
    if($source !== ''){
        $context['source'] = $source;
    }
    logWarningToFile($fileName,$message,$context);
}

/**
 * Shorthand to log error into a 'waboot-log'
 *
 * @param string $message
 * @param string $source
 * @param array $context
 * @param string $fileName
 * @return void
 */
function logError(string $message, string $source, array $context = [], string $fileName = 'waboot-log'): void {
    if($source !== ''){
        $context['source'] = $source;
    }
    logErrorToFile($fileName,$message,$context);
}

/**
 * Shorthand to log an exception into a 'waboot-log'
 *
 * @param \Exception|\Throwable $e
 * @param string $source
 * @param array $context
 * @param string $fileName
 * @return void
 */
function logException(\Exception|\Throwable $e, string $source, array $context = [], string $fileName = 'waboot-log'): void {
    if($source !== ''){
        $context['source'] = $source;
    }
    logErrorToFile($fileName,$e->getMessage(),$context);
}