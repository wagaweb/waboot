<?php

namespace Waboot\inc\core\cli;

trait CommandLogger
{
    /**
     * @param $name
     * @return \Monolog\Logger
     * @throws \Exception
     */
    private function getLogger($name): \Monolog\Logger{
        static $logger;
        if(isset($logger)){
            return $logger;
        }
        if(!class_exists('Monolog\Logger')){
            throw new \RuntimeException('Monolog not installed');
        }
        $logger = new \Monolog\Logger($name);
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($this->getLogFile()), \Monolog\Logger::INFO);
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
