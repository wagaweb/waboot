<?php

namespace Waboot\inc\core\cli;

use Waboot\inc\core\alert\Alert;
use Waboot\inc\core\alert\AlertDispatcher;
use Waboot\inc\core\alert\AlertDispatcherException;
use Waboot\inc\core\alert\AlertDispatcherFactory;
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
    protected string $defaultAlertDispatchEmail;
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
        if(LoggerFactory::monologExists()){
            try{
                $this->logger = $this->getLogger('waboot-cli-command-logger', $this->getTimeZone());
            }catch (LoggerFactoryException $e){
                $this->error('Unable to initialize the logger: '.$e->getMessage(), false);
            }
        }
    }

    /**
     * @return array
     */
    public static function getCommandDescription(): array
    {
        //@see: https://make.wordpress.org/cli/handbook/guides/commands-cookbook/#wp_cliadd_commands-third-args-parameter
        return [
            'shortdesc' => 'A simple command',
            'longdesc' => '## EXAMPLES' . "\n\n" . 'wp simple-command',
            'synopsis' => [
                /*[
                    'type'        => 'positional',
                    'name'        => 'name',
                    'description' => 'The name of the person to greet.',
                    'optional'    => false,
                    'repeating'   => false,
                ],
                [
                    'type'        => 'assoc',
                    'name'        => 'type',
                    'description' => 'Whether or not to greet the person with success or error.',
                    'optional'    => true,
                    'default'     => 'success',
                    'options'     => array( 'success', 'error' ),
                ],*/
                [
                    'type' => 'flag',
                    'name' => 'be-quiet',
                    'description' => 'Turns off verbose',
                    'optional' => true,
                ],
                [
                    'type' => 'flag',
                    'name' => 'dry-run',
                    'description' => 'Perform a dry run',
                    'optional' => true,
                ],
            ],
            //'when' => 'after_wp_load', //before_wp_load
        ];
    }

    /**
     * @param array $args
     * @param array $assoc_args
     * @return void
     */
    /*public function __invoke(array $args, array $assoc_args)
    {
        $this->setupDefaultFlags($assoc_args);
        if($this->isDryRun()){
            $this->log('### DRY-RUN ###');
        }
    }*/

    /**
     * @param array $args
     * @param array $assoc_args
     * @return int|void
     */
    public function __invoke(array $args, array $assoc_args)
    {
        if(method_exists($this,'run')){
            try{
                $this->setupDefaultFlags($assoc_args);
                if($this->isDryRun()){
                    $this->log('### DRY-RUN ###');
                }
                $this->beginCommandExecution();
                $r = $this->run($args,$assoc_args);
                $this->endCommandExecution();
                return $r;
            }catch (CLIRuntimeException | \Exception | \Throwable $e){
                $this->endCommandExecution();
                $this->error($e->getMessage(), false);
                return 1;
            }
        }else{
            $this->setupDefaultFlags($assoc_args);
            if($this->isDryRun()){
                $this->log('### DRY-RUN ###');
            }
        }
    }

    /**
     * @param array $args
     * @param array $assoc_args
     * @return int
     */
    public function run(array $args, array $assoc_args): int
    {
        return 0;
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
            //--quiet is built in in WordPress and prevent anything to be printed out
            $this->verbose = false;
        }
        if(isset($args['be-quiet'])) {
            //...so we need this to being able to implement a flexible verbose\silent behavior
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
     * @param array $context
     * @param string $type
     * @param $printToCli
     * @param bool $dieOnError
     * @return void
     */
    protected function handleLogEvent(string $message, array $context = [], string $type = 'log', $printToCli = null, bool $dieOnError = false): void
    {
        if($printToCli === null){
            $printToCli = $this->isVerbose();
        }
        switch ($type){
            case 'error':
                try{
                    if($this->mustLog() && $this->canLog()){
                        $this->logger->error($message, $context);
                    }
                    if($this->isWPCLI() && $printToCli){
                        \WP_CLI::error($message,$dieOnError);
                    }
                    if($dieOnError){
                        die();
                    }
                }catch(\WP_CLI\ExitException $e){
                    \WP_CLI::log($message);
                    if($dieOnError){
                        die();
                    }
                }
                break;
            case 'success':
                if($this->isWPCLI() && $printToCli){
                    \WP_CLI::success($message);
                }
                if($this->mustLog() && $this->canLog()){
                    $this->logger->info($message);
                }
                break;
            case 'warning':
                if($this->isWPCLI() && $printToCli){
                    \WP_CLI::warning($message);
                }
                if($this->mustLog() && $this->canLog()){
                    $this->logger->info('Warning: '.$message,$context);
                }
                break;
            case 'log':
            default:
                if($this->isWPCLI() && $printToCli){
                    \WP_CLI::log($message);
                }
                if($this->mustLog() && $this->canLog()){
                    $this->logger->info($message,$context);
                }
                break;
        }
    }

    /**
     * @param string $message
     * @param bool|null $printToCli
     * @param array $context
     */
    protected function log(string $message, bool $printToCli = null, array $context = []): void
    {
        $this->handleLogEvent($message, $context, 'log', $printToCli);
    }

    /**
     * @param string $message
     * @param bool|null $printToCli
     * @param array $context
     */
    protected function warning(string $message, bool $printToCli = null, array $context = []): void
    {
        $this->handleLogEvent($message, $context, 'warning', $printToCli);
    }

    /**
     * @param string $message
     * @param bool $die
     */
    protected function error(string $message, bool $die = true): void
    {
        $this->handleLogEvent($message, [], 'error', null, $die);
    }

    /**
     * @param string $message
     * @param bool|null $printToCli
     */
    protected function success(string $message, bool $printToCli = null): void
    {
        $this->handleLogEvent($message, [], 'success', $printToCli);
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
     * @return void
     * @throws \Exception
     */
    protected function setupAlertDispatcher(): void
    {
        if(!isset($this->defaultAlertDispatchEmail)){
            return;
        }
        $this->alertDispatcher = AlertDispatcherFactory::createEmailDispatcher($this->logDirName,$this->defaultAlertDispatchEmail,$this->getTimeZone());
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
        $today = Dates::getToday($this->getTimeZone());
        $this->alertDispatcher->addAlert(
            new Alert(
                sanitize_title($this->logDirName).'maybe-stuck',
                '['.$today->format('Y/m/d H:i').'] Stuck error',
                'Script seems stuck.',
                $this->timeZone
            )
        );
        $this->alertDispatcher->dispatch();
    }

    /**
     * @return \DateTimeZone
     * @throws \Exception
     */
    protected function getTimeZone(): \DateTimeZone
    {
        if(!isset($this->timeZone) || !Dates::isValidTimezone($this->timeZone)){
            return Dates::getDefaultDateTimeZone();
        }
        return new \DateTimeZone($this->timeZone);
    }
}