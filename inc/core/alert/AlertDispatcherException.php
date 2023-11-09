<?php

namespace Waboot\inc\core\alert;

class AlertDispatcherException extends \Exception {
    /**
     * @var Alert[]
     */
    protected $alerts;

    public function __construct($message = "", $code = 0, \Throwable $previous = null, array $alerts = null)
    {
        if($alerts !== null){
            $this->setAlerts($alerts);
        }
        parent::__construct($message,$code,$previous);
    }

    /**
     * @param array $alerts
     */
    public function setAlerts(array $alerts): void
    {
        $alerts = array_filter($alerts, static function($alert){
            return $alert instanceof Alert;
        });
        if(\is_array($alerts) && count($alerts) > 0){
            $this->alerts = $alerts;
        }
    }

    /**
     * @return array|Alert[]
     */
    public function getAlerts(): array
    {
        return $this->alerts ?? [];
    }

    /**
     * @return string
     */
    public function getAlertsMessages(): string
    {
        $message = '';
        foreach ($this->getAlerts() as $alert){
            $message .= '###['.$alert->getMessage().']###';
        }
        return $message;
    }
}