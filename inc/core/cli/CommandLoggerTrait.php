<?php

namespace Waboot\inc\core\cli;

use Waboot\inc\core\LoggerFactory;
use Waboot\inc\core\LoggerFactoryException;
use Waboot\inc\core\utils\Dates;

trait CommandLoggerTrait
{
    /**
     * @param $name
     * @param \DateTimeZone|null $tz
     * @return \Monolog\Logger
     * @throws LoggerFactoryException
     * @throws \Exception
     */
    private function getLogger($name,\DateTimeZone $tz = null): \Monolog\Logger
    {
        if(!isset($tz)){
            $tz = Dates::getDefaultDateTimeZone();
        }
        return LoggerFactory::create($name,$this->getLogFile($tz),$tz);
    }

    /**
     * @return string
     */
    private function getLogsDir(): string
    {
        return WP_CONTENT_DIR.'/cli-logs/'.$this->logDirName;
    }

    /**
     * @param \DateTimeZone|null $tz
     * @return string
     * @throws \Exception
     */
    private function getLogFile(\DateTimeZone $tz = null): string
    {
        static $logFile;
        if(isset($logFile)){
            return $logFile;
        }
        if(!isset($tz)){
            $tz = Dates::getDefaultDateTimeZone();
        }
        $logFile = $this->getLogsDir().'/'.$this->logFileName.'-'.Dates::getToday($tz)->format('Y-m-d').'.log';
        return $logFile;
    }
}
