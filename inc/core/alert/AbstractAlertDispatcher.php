<?php

namespace Waboot\inc\core\alert;

use Waboot\inc\core\utils\Dates;

abstract class AbstractAlertDispatcher implements AlertDispatcherInterface
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var Alert[]
     */
    protected $alerts;
    /**
     * @var \DateTimeZone
     */
    protected $timeZone;

    /**
     * @param string $name
     * @param string|null $tz
     */
    public function __construct(string $name, string $tz = null)
    {
        $this->name = $name;
        if(isset($tz)){
            $timeZone = Dates::getDateTimeZoneFromString($tz);
        }else{
            $timeZone = Dates::getDefaultDateTimeZone();
        }
        $this->timeZone = $timeZone;
    }

    /**
     * @param Alert $alert
     */
    public function addAlert(Alert $alert): void
    {
        $this->alerts[] = $alert;
    }

    /**
     * @return bool
     */
    public function hasAlerts(): bool
    {
        return \is_array($this->alerts) && count($this->alerts) > 0;
    }

    abstract function dispatch(): void;
}