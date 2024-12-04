<?php

namespace Waboot\inc\core;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Waboot\inc\core\utils\Dates;

// https://github.com/Seldaek/monolog
// https://seldaek.github.io/monolog/doc/01-usage.html

class LoggerFactory
{
    /**
     * @param string $name
     * @param string $logFileName
     * @param \DateTimeZone|null $tz
     * @param array $params
     * @return Logger
     * @throws LoggerFactoryException
     */
    public static function create(string $name, string $logFileName, \DateTimeZone $tz = null, array $params = []): Logger
    {
        if(!self::monologExists()){
            throw new LoggerFactoryException('Monolog not installed');
        }
        $params = wp_parse_args($params, [
            'level' => Logger::DEBUG,
            'dateFormat' => 'Y-m-d\TH:i:s', // the default date format is "Y-m-d\TH:i:sP"
            'outputFormat' => "[%datetime%][%channel%][%level_name%]: %message% %context% %extra%\n", // the default output format is "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
            'formatter' => null,
        ]);
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
            $stream = new StreamHandler($logFileName,$params['level']);
            // Formatting:
            if(!$params['formatter']){
                $formatter = new LineFormatter($params['outputFormat'], $params['dateFormat']);
                $stream->setFormatter($formatter);
            }else{
                $stream->setFormatter($params['formatter']);
            }
            $logger->pushHandler($stream);
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