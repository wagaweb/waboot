<?php

namespace Waboot\inc\core\alert\dispatcher;

use Sentry\Severity;
use waboot\inc\core\alert\AbstractAlertDispatcher;
use Waboot\inc\core\alert\AlertDispatcherException;
use Waboot\inc\core\LoggerFactory;
use Waboot\inc\core\LoggerFactoryException;

class SentryAlertDispatcher extends AbstractAlertDispatcher
{
    /**
     * @var array
     */
    protected array $sentryArgs;

    /**
     * @param string $name
     * @param array $sentryArgs
     * @param string|null $tz
     */
    public function __construct(string $name, array $sentryArgs, string $tz = null)
    {
        parent::__construct($name,$tz);
        $this->name = $name;
        $this->sentryArgs = $sentryArgs;
    }

    function dispatch(): void
    {
        try{
            LoggerFactory::createSentryLogger($this->sentryArgs);
            foreach ($this->alerts as $alert){
                \Sentry\captureMessage($alert->getMessage(),Severity::error());
            }
        }catch (LoggerFactoryException $e) {
            throw new AlertDispatcherException($e->getMessage());
        }
    }
}
