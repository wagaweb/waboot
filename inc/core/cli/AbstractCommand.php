<?php

namespace Waboot\inc\core\cli;

use Waboot\inc\core\Alert;
use Waboot\inc\core\AlertDispatcher;
use Waboot\inc\core\AlertDispatcherException;
use Waboot\inc\core\LoggerFactory;
use Waboot\inc\core\LoggerFactoryException;
use Waboot\inc\core\utils\Dates;

class AbstractCommand
{
    use CommandLoggerTrait;

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
     * @var string
     */
    protected $logMarker;
    /**
     * @var bool
     */
    protected $showProgressBar = false;
    /**
     * @var bool
     */
    protected $dryRun = false;
    /**
     * @var AlertDispatcher
     */
    protected $alertDispatcher;
    /**
     * @var string
     */
    protected $timeZone;
    /**
     * @var string
     * @see: https://www.php.net/manual/en/dateinterval.createfromdatestring.php
     * @see: https://www.php.net/manual/en/class.dateinterval.php
     */
    protected $allowedDurationInterval = '1 day';

    public function __construct()
    {
        if(LoggerFactory::logsHandlerExists()){
            try{
                $this->logger = $this->getLogger('waboot-cli-command-logger', $this->getTimeZone());
            }catch (LoggerFactoryException $e){
                $this->error('Unable to initialize the logger: '.$e->getMessage(), false);
            }
        }
    }

    public function __invoke(array $args, array $assoc_args)
    {
        $this->setupDefaultFlags($assoc_args);
    }

    protected function suppressErrors(): void
    {
        error_reporting(0);
    }

    protected function suppressWarnings(): void
    {
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
        $this->logMarker = $assoc_args['marker'] ?? null;
    }

    /**
     * @return void
     */
    protected function beginCommandExecution(): void
    {
        if(isset($this->logMarker)){
            $this->log('### '.$this->logMarker.' BEGIN');
        }
        $this->setStartStateOptions();
    }

    /**
     * @return void
     */
    protected function endCommandExecution(): void
    {
        if(isset($this->logMarker)){
            $this->log('### '.$this->logMarker.' END');
        }
        $this->setEndStateOptions();
    }

    /**
     * @param string $message
     * @param bool $printToCli
     * @param array $context
     */
    protected function log(string $message, bool $printToCli = true, array $context = []): void
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

    /**
     * @param string $message
     * @param bool $die
     */
    protected function error(string $message, bool $die = true): void
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

    /**
     * @param string $message
     */
    protected function success(string $message): void
    {
        if($this->mustLog() && $this->canLog()){
            $this->logger->info($message);
        }
        if($this->isWPCLI() && $this->isVerbose()){
            \WP_CLI::success($message);
        }
    }

    /**
     * @return bool
     */
    protected function isWPCLI(): bool
    {
        return defined('WP_CLI') && WP_CLI;
    }

    /**
     * @return bool
     */
    protected function isVerbose(): bool
    {
        return $this->verbose && !$this->showProgressBar;
    }

    /**
     * @return bool
     */
    protected function canLog(): bool
    {
        return $this->logger instanceof \Monolog\Logger;
    }

    /**
     * @return bool
     */
    protected function mustLog(): bool
    {
        return $this->skipLog === false;
    }

    /**
     * @return bool
     */
    protected function progressBarAvailable(): bool
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

    /**
     * @return string
     */
    private function getStateOptionNameSuffix(): string
    {
        return 'cli_'.sanitize_title($this->logFileName);
    }

    /**
     * @return void
     */
    private function setStartStateOptions(): void
    {
        try{
            $today = Dates::getToday($this->getTimeZone());
            update_option($this->getStateOptionNameSuffix().'_process_in_progress','yes');
            update_option($this->getStateOptionNameSuffix().'_last_started_at', $today->format('Y-m-d_H-i'));
            update_option($this->getStateOptionNameSuffix().'_last_started_process_id', $today->format('U'));
        }catch (\Exception $e){
            $this->error($e->getMessage(),false);
        }
    }

    /**
     * @return void
     */
    private function setEndStateOptions(): void
    {
        try{
            $today = Dates::getToday($this->getTimeZone());
            delete_option($this->getStateOptionNameSuffix().'_process_in_progress');
            update_option($this->getStateOptionNameSuffix().'_last_ended_at', $today->format('Y-m-d_H-i'));
            $processId = get_option($this->getStateOptionNameSuffix().'_started_process_id');
            update_option($this->getStateOptionNameSuffix().'_last_ended_process_id', $processId);
        }catch (\Exception $e){
            $this->error($e->getMessage(),false);
        }
    }

    /**
     * @return bool
     */
    protected function isRunning(): bool
    {
        return get_option($this->getStateOptionNameSuffix().'_process_in_progress') === 'yes';
    }

    /**
     * @return bool
     */
    protected function isRunningFine(): bool
    {
        return $this->isRunning() && !$this->isBlocked();
    }

    /**
     * @return bool
     */
    protected function isBlocked(): bool
    {
        if(!$this->hasRunOnce()){
            return false;
        }
        try{
            $tz = $this->getTimeZone();
            $today = Dates::getToday($tz);
            $inProgressOpt = get_option($this->getStateOptionNameSuffix().'_process_in_progress');
            $startedOpt = get_option($this->getStateOptionNameSuffix().'_last_started_at');
            $startedDateTime = date_create_from_format('Y-m-d_H-i',$startedOpt,$tz);
            $dateInterval = \DateInterval::createFromDateString($this->allowedDurationInterval);
            if(!$dateInterval instanceof \DateInterval){
                throw new \RuntimeException('Invalid date interval');
            }
            $maxValidEndDateTime = $startedDateTime->add($dateInterval);
            return $today > $maxValidEndDateTime && $inProgressOpt === 'yes';
        }catch (\Exception $e){
            $this->error($e->getMessage(),false);
            return false;
        }
    }

    /**
     * @return bool
     */
    protected function hasRunOnce(): bool
    {
        $startedOpt = get_option($this->getStateOptionNameSuffix().'_last_started_at');
        return $startedOpt !== false;
    }

    /**
     * @throws AlertDispatcherException
     * @throws \Exception
     */
    protected function dispatchScriptStuckAlert(): void
    {
        if(!isset($this->alertDispatcher)){
            return;
        }
        if(!$this->isBlocked()){
            return;
        }
        $this->alertDispatcher->addAlert(
            new Alert(
                sanitize_title($this->logDirName).'maybe-stuck',
                'Stuck error',
                'Script seems stuck.',
                $this->timeZone
            )
        );
        $this->alertDispatcher->dispatch();
    }

    /**
     * @return \DateTimeZone
     */
    protected function getTimeZone(): \DateTimeZone
    {
        if(!isset($this->timeZone) || !Dates::isValidTimezone($this->timeZone)){
            return Dates::getDefaultDateTimeZone();
        }
        return new \DateTimeZone($this->timeZone);
    }
}