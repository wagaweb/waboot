<?php

namespace Waboot\inc\core\cli;

use Waboot\inc\core\LoggerFactory;
use Waboot\inc\core\LoggerFactoryException;

trait CommandLoggerTrait
{
    /**
     * @param $name
     * @return \Monolog\Logger
     * @throws LoggerFactoryException
     */
    private function getLogger($name): \Monolog\Logger{
        return LoggerFactory::create($name,$this->getLogFile());
    }

    private function getLogsDir(): string {
        return WP_CONTENT_DIR.'/cli-logs/'.$this->logDirName;
    }

    private function getLogFile(): string {
        static $logFile;
        if(isset($logFile)){
            return $logFile;
        }
        $logFile = $this->getLogsDir().'/'.$this->logFileName.'-'.(new \DateTime())->format('Y-m-d').'.log';
        return $logFile;
    }
}
