<?php

namespace Waboot\addons\packages\catalog_custom_tables\inc;

use Monolog\Logger;

trait CommandLogger
{
    /**
     * @param $name
     * @return Logger
     * @throws \Exception
     */
    private function getLogger($name): Logger{
        static $logger;
        if(isset($logger)){
            return $logger;
        }
        $logger = new Logger($name);
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($this->getLogFile()), Logger::INFO);
        return $logger;
    }

    private function initLogFile(): bool {
        if(!\is_dir($this->getLogsDir())){
            $r = wp_mkdir_p($this->getLogsDir());
            if(!$r) {
                return false;
            }
        }
        $logFile = $this->getLogFile();
        if(!\is_file($logFile)){
            return touch($logFile);
        }
        return true;
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
