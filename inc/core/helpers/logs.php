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
function logToFile(string $loggerIdentifier, string $logMessage, int $logLevel = MonologLoggingLevels::INFO, array $context = [], \DateTimeZone $dz = null): void {
    Waboot()->logToFile($loggerIdentifier,$logMessage,$logLevel,$context,$dz);
}

/**
 * @param string $loggerIdentifier
 * @param string $logMessage
 * @param array $context
 * @param \DateTimeZone|null $dz
 * @return void
 */
function logInfoToFile(string $loggerIdentifier, string $logMessage, array $context = [], \DateTimeZone $dz = null): void {
    Waboot()->logToFile($loggerIdentifier,$logMessage,MonologLoggingLevels::INFO,$context,$dz);
}

/**
 * @param string $loggerIdentifier
 * @param string $logMessage
 * @param array $context
 * @param \DateTimeZone|null $dz
 * @return void
 */
function logWarningToFile(string $loggerIdentifier, string $logMessage, array $context = [], \DateTimeZone $dz = null): void {
    Waboot()->logToFile($loggerIdentifier,$logMessage,MonologLoggingLevels::WARNING,$context,$dz);
}

/**
 * @param string $loggerIdentifier
 * @param string $logMessage
 * @param array $context
 * @param \DateTimeZone|null $dz
 * @return void
 */
function logErrorToFile(string $loggerIdentifier, string $logMessage, array $context = [], \DateTimeZone $dz = null): void {
    Waboot()->logToFile($loggerIdentifier,$logMessage,MonologLoggingLevels::ERROR,$context,$dz);
}