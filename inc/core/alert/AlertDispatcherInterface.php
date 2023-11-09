<?php

namespace Waboot\inc\core\alert;

interface AlertDispatcherInterface
{
    /**
     * @param Alert $alert
     * @return void
     */
    public function addAlert(Alert $alert): void;

    /**
     * @return bool
     */
    public function hasAlerts(): bool;

    /**
     * @throws AlertDispatcherException
     * @return void
     */
    public function dispatch(): void;
}