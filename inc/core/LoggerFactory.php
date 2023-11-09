<?php

namespace Waboot\inc\core;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Waboot\inc\core\utils\Dates;

class LoggerFactory
{
    /**
     * @param string $name
     * @param string $logFileName
     * @param \DateTimeZone|null $tz
     * @return Logger
     * @throws LoggerFactoryException
     */
    public static function create(string $name, string $logFileName, \DateTimeZone $tz = null): Logger
    {
        if(!self::monologExists()){
            throw new LoggerFactoryException('Monolog not installed');
        }
        $pInfo = pathinfo($logFileName);
        if(!\is_dir($pInfo['dirname'])){
            $r = wp_mkdir_p($pInfo['dirname']);
            if(!$r) {
                throw new LoggerFactoryException('Unable to create log directory: '.$pInfo['dirname']);
            }
        }
        if(!\is_file($logFileName) && !touch($logFileName)){
            throw new LoggerFactoryException('Unable to write log file: '.$logFileName);
        }
        try{
            if(!isset($tz)){
                $tz = Dates::getDefaultDateTimeZone();
            }
            $logger = new Logger($name,[],[],$tz);
            $logger->pushHandler(new StreamHandler($logFileName), Logger::INFO);
            return $logger;
        }catch (\Exception $e) {
            throw new LoggerFactoryException($e->getMessage());
        }
    }

    /**
     * @param array $args
     * @return boolean
     * @throws LoggerFactoryException
     */
    public static function createSentryLogger(array $args): bool
    {
        if(!self::sentryExists()){
            throw new LoggerFactoryException('Sentry non installed, use "composer require sentry/sdk"');
        }
        \Sentry\init($args);
        return true;
    }

    public static function monologExists(): bool
    {
        return class_exists('Monolog\Logger');
    }

    public static function sentryExists(): bool
    {
        return function_exists('\Sentry\captureException');
    }
}