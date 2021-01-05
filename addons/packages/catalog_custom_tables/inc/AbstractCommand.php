<?php

namespace Waboot\addons\packages\catalog_custom_tables\inc;

use Monolog\Logger;

class AbstractCommand
{
    use CommandLogger;

    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var string
     */
    protected $logDirName = 'common';
    /**
     * @var string
     */
    protected $logFileName = 'common';
    /**
     * @var bool
     */
    protected $verbose = true;
    /**
     * @var bool
     */
    protected $skipLog = false;
    /**
     * @var bool
     */
    protected $showProgressBar = false;

    public function __construct()
    {
        $this->initLogFile();
        try{
            $this->logger = $this->getLogger('esperiri-cli-command-logger');
        }catch (\Exception $e){
            //WP_CLI::error('Unable to initialize the logger');
        }
    }

    protected function suppressErrors(): void {
        error_reporting(0);
    }

    protected function suppressWarnings(): void {
        //error_reporting(E_ERROR | E_PARSE);
        error_reporting(E_ALL ^ E_WARNING);
    }

    /**
     * @param array $args associative command argument
     */
    protected function setupDefaultFlags(array $args): void {
        if(isset($args['quiet'])) {
            $this->verbose = false;
        }
        if(isset($args['progress'])){
            $this->showProgressBar = true;
        }
    }

    protected function log(string $message){
        if($this->isWPCLI() && $this->isVerbose()){
            \WP_CLI::log($message);
        }
        if($this->mustLog()){
            $this->logger->info($message);
        }
    }

    protected function error(string $message, $die = true){
        try{
            if($this->mustLog()){
                $this->logger->error($message);
            }
            if($die && $this->isWPCLI()){
                \WP_CLI::error($message);
            }
        }catch(\WP_CLI\ExitException $e){
            \WP_CLI::log($message);
            if($die){
                die();
            }
        }
    }

    protected function success(string $message){
        if($this->mustLog()){
            $this->logger->info($message);
        }
        if($this->isWPCLI() && $this->isVerbose()){
            \WP_CLI::success($message);
        }
    }

    protected function isWPCLI(){
        return defined('WP_CLI') && WP_CLI;
    }

    protected function isVerbose(): bool {
        return $this->verbose && !$this->showProgressBar;
    }

    protected function mustLog(): bool {
        return $this->skipLog === false;
    }

    /**
     * @return bool
     */
    protected function progressBarAvailable(){
        return function_exists('\WP_CLI\Utils\make_progress_bar');
    }

    /**
     * @param $item
     * @return bool
     */
    protected function isProgressBar($item): bool {
        return $item instanceof \cli\progress\Bar;
    }

    /**
     * @param $message
     * @param $count
     * @return \cli\progress\Bar|WP_CLI\NoOp|bool
     */
    protected function makeProgressBar($message, $count){
        if($this->progressBarAvailable()){
            $progress = \WP_CLI\Utils\make_progress_bar( $message, $count );
            return $progress;
        }
        return false;
    }
}