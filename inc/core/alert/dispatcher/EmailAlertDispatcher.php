<?php

namespace Waboot\inc\core\alert\dispatcher;

use waboot\inc\core\alert\AbstractAlertDispatcher;
use Waboot\inc\core\alert\AlertDispatcherException;
use Waboot\inc\core\Mail;
use Waboot\inc\core\MailAddress;
use Waboot\inc\core\MailException;

class EmailAlertDispatcher extends AbstractAlertDispatcher
{
    /**
     * @var string
     */
    protected string $dispatchTo;
    /**
     * @var callable
     */
    protected $mailHandlerCallback;

    /**
     * @param string $name
     * @param string $dispatchTo
     * @param string|null $tz
     */
    public function __construct(string $name, string $dispatchTo, string $tz = null)
    {
        parent::__construct($name,$tz);
        $this->name = $name;
        $this->dispatchTo = $dispatchTo;
    }

    /**
     * @param callable $callable
     */
    public function setMailHandlerCallback(callable $callable): void
    {
        $this->mailHandlerCallback = $callable;
    }

    /**
     * @return void
     * @throws AlertDispatcherException
     */
    public function dispatch(): void
    {
        $mailTitle = $this->name.': errors occurred';
        $mailBody = '';
        foreach ($this->alerts as $alert){
            $mailBody .= "### ".$alert->getTimeStamp()."\r\n";
            $mailBody .= $alert->getTitle()."\r\n";
            $mailBody .= $alert->getMessage()."\r\n";
            $mailBody .= "###\r\n\r\n";
        }
        $r = $this->sendAlertMail($mailTitle,$mailBody,$this->dispatchTo);
        if($r ===  false){
            throw new AlertDispatcherException('Unable to send the alert email to: '.$this->dispatchTo,0,null,$this->alerts);
        }
    }

    /**
     * @param $title
     * @param $body
     * @param $to
     * @return bool
     */
    private function sendAlertMail($title,$body,$to): bool
    {
        if(isset($this->mailHandlerCallback) && is_callable($this->mailHandlerCallback)){
            $callback = $this->mailHandlerCallback;
            try{
                return $callback($title,$body,$to);
            }catch (\TypeError $e){
                return false;
            }
        }
        try {
            return (new Mail($title, $body, new MailAddress($to)))->send();
        } catch (MailException $e) {
            return false;
        }
    }
}