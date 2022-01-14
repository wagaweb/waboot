<?php

namespace Waboot\inc\core;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LoggerFactory
{
    /**
     * @param string $name
     * @param string $logFileName
     * @return void
     * @throws LoggerFactoryException
     */
    public static function create(string $name, string $logFileName): Logger
    {

        if(!self::logsHandlerExists()){
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
            $logger = new Logger($name);
            $logger->pushHandler(new StreamHandler($logFileName), Logger::INFO);
            return $logger;
        }catch (\Exception $e) {
            throw new LoggerFactoryException($e->getMessage());
        }
    }

    public static function logsHandlerExists(): bool
    {
        return class_exists('Monolog\Logger');
    }
}