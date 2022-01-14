<?php

namespace Waboot\inc\core;

class AlertDispatcher
{
    public const DISPATCH_METHOD_EMAIL = 'email';
    public const DISPATCH_METHOD_FILE = 'file';
    /**
     * @var string
     */
    protected $name;
    /**
     * @var Alert[]
     */
    protected $alerts;
    /**
     * @var string
     */
    protected $dispatchMethod;
    /**
     * @var callable
     */
    protected $mailHandlerCallback;
    /**
     * @var string
     */
    protected $dispatchTo;
    /**
     * @var \DateTimeZone
     */
    protected $timeZone;

    /**
     * @param string $name
     * @param string $dispatchMethod
     * @param string $dispatchTo
     * @param string|null $tz
     */
    public function __construct(string $name, string $dispatchMethod, string $dispatchTo, string $tz = null)
    {
        $this->name = $name;
        if(!\in_array($dispatchMethod,[self::DISPATCH_METHOD_FILE,self::DISPATCH_METHOD_EMAIL],true)){
            throw new \RuntimeException('Invalid dispatch method');
        }
        $this->dispatchMethod = $dispatchMethod;
        $this->dispatchTo = $dispatchTo;
        if(isset($tz)){
            $timeZone = new \DateTimeZone($tz);
            $this->timeZone = $timeZone;
        }
    }

    /**
     * @param Alert $alert
     */
    public function addAlert(Alert $alert): void
    {
        $this->alerts[] = $alert;
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
        switch($this->dispatchMethod){
            case self::DISPATCH_METHOD_EMAIL:
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
                break;
            case self::DISPATCH_METHOD_FILE:
                $fileContent = '';
                foreach ($this->alerts as $alert){
                    $fileContent .= '### '.$alert->getTimeStamp().PHP_EOL;
                    $fileContent .= $alert->getTitle().PHP_EOL;
                    $fileContent .= $alert->getMessage().PHP_EOL;
                    $fileContent .= '###'.PHP_EOL.PHP_EOL;
                }
                if(!\is_dir($this->dispatchTo) && !\wp_mkdir_p($this->dispatchTo)){
                    throw new AlertDispatcherException('Unable to write the alert log to: '.$this->dispatchTo.': directory not found',0,null,$this->alerts);
                }
                try {
                    $now = new \DateTime('now', $this->timeZone);
                } catch (\Exception $e) {
                    throw new AlertDispatcherException($e->getMessage());
                }
                $fileName = $now->format('Y-m-d_h-i_').sanitize_title($this->name).'.alerts';
                $filePath = $this->dispatchTo.'/'.$fileName;
                $r = file_put_contents($filePath,$fileContent,FILE_APPEND);
                if($r ===  false){
                    throw new AlertDispatcherException('Unable to write the alert log to: '.$filePath,0,null,$this->alerts);
                }
                break;
        }
    }

    /**
     * @param $title
     * @param $body
     * @param $to
     * @return bool
     */
    public function sendAlertMail($title,$body,$to): bool
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

    /**
     * @return bool
     */
    public function hasAlerts(): bool
    {
        return \is_array($this->alerts) && count($this->alerts) > 0;
    }
}