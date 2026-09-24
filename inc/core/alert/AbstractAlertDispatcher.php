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
     * @param \DateTimeZone|null $tz
     */
    public function __construct(string $name, ?\DateTimeZone $tz = null)
    {
        $this->name = $name;
        $this->timeZone = $tz ?? Dates::getDefaultDateTimeZone();
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