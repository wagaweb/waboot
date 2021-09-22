<?php

namespace Waboot\inc\core\cli;

class AbstractCommand
{
    use CommandLogger;

    /**
     * @var \Monolog\Logger
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
    protected $tmpVerbose;
    /**
     * @var bool
     */
    protected $skipLog = false;
    /**
     * @var bool
     */
    protected $showProgressBar = false;
    /**
     * @var bool
     */
    protected $dryRun = false;

    public function __construct()
    {
        if($this->logsHandlerExists()){
            $this->initLogFile();
            try{
                $this->logger = $this->getLogger('waboot-cli-command-logger');
            }catch (\Exception $e){
                $this->error('Unable to initialize the logger: '.$e->getMessage(), false);
            }
        }
    }

    public function __invoke($args, $assoc_args){
        $this->setupDefaultFlags($assoc_args);
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
    protected function setupDefaultFlags(array $args): void
    {
        if(isset($args['dry-run'])) {
            $this->dryRun = true;
        }
        if(isset($args['quiet'])) {
            $this->verbose = false;
        }
        if(isset($args['progress'])){
            $this->showProgressBar = true;
        }
    }

    protected function log(string $message, $printToCli = true, $context = [])
    {
        if($this->isWPCLI() && $this->isVerbose()){
            if($printToCli){
                \WP_CLI::log($message);
            }
        }
        if($this->mustLog() && $this->canLog()){
            $this->logger->info($message,$context);
        }
    }

    protected function error(string $message, $die = true)
    {
        try{
            if($this->mustLog() && $this->canLog()){
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

    protected function success(string $message)
    {
        if($this->mustLog() && $this->canLog()){
            $this->logger->info($message);
        }
        if($this->isWPCLI() && $this->isVerbose()){
            \WP_CLI::success($message);
        }
    }

    protected function isWPCLI()
    {
        return defined('WP_CLI') && WP_CLI;
    }

    protected function isVerbose(): bool
    {
        return $this->verbose && !$this->showProgressBar;
    }

    protected function canLog(): bool
    {
        return $this->logger instanceof \Monolog\Logger;
    }

    protected function mustLog(): bool
    {
        return $this->skipLog === false;
    }

    protected function logsHandlerExists(): bool
    {
        return class_exists('Monolog\Logger');
    }

    /**
     * @return bool
     */
    protected function progressBarAvailable()
    {
        return function_exists('\WP_CLI\Utils\make_progress_bar');
    }

    /**
     * @param $item
     * @return bool
     */
    protected function isProgressBar($item): bool
    {
        return $item instanceof \cli\progress\Bar;
    }

    /**
     * @param $message
     * @param $count
     * @return \cli\progress\Bar|WP_CLI\NoOp|bool
     */
    protected function makeProgressBar($message, $count)
    {
        if($this->progressBarAvailable()){
            $progress = \WP_CLI\Utils\make_progress_bar( $message, $count );
            $this->tmpVerbose = $this->verbose;
            $this->verbose = false;
            return $progress;
        }
        return false;
    }

    /**
     * @param mixed $item
     */
    protected function tickProgressBar($item): void
    {
        if(!$this->isProgressBar($item)){
            return;
        }
        $item->tick();
    }

    /**
     * @param mixed $item
     */
    protected function completeProgressBar($item): void
    {
        if(!$this->isProgressBar($item)){
            return;
        }
        $item->finish();
        $this->verbose = $this->tmpVerbose;
        $this->tmpVerbose = null;
    }

    /**
     * @return bool
     */
    protected function isDryRun(): bool
    {
        return $this->dryRun;
    }
}