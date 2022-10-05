<?php

namespace Waboot\inc\core;

use Waboot\inc\core\utils\Dates;

class LoggableException extends \Exception
{
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return void
     */
    public function log(): void
    {
        if(!LoggerFactory::logsHandlerExists()){
            return;
        }
        try{
            $tz = Dates::getDefaultDateTimeZone();
            $filename = trailingslashit(WP_CONTENT_DIR) . 'logs/waboot-exceptions-'.Dates::getToday($tz)->format('Y-m-d').'.log';
            $logger = LoggerFactory::create('waboot-exception-logger',$filename,$tz);
            $logger->debug($this->getMessage());
        } catch (\Exception | \Throwable $e) {}
    }

    /**
     * @return void
     */
    public function publish(): void
    {
        if(!LoggerFactory::remoteLogsHandlerExists()){
            return;
        }
        try {
            LoggerFactory::createRemoteLogger();
            try{
                throw new \RuntimeException($this->getMessage());
            }catch (\RuntimeException $e){
                \Sentry\captureException($e);
            }
        } catch (LoggerFactoryException $e) {}
    }

    /**
     * @param string|null $email
     * @return void
     */
    public function dispatchTo(string $email = null): void
    {
        if(!$email){
            $email = get_bloginfo('admin_email');
        }
        $siteName = get_bloginfo('name');
        $mailTitle = $siteName.': errors occurred';
        $mailBody = $this->getMessage();
        try {
            (new Mail($mailTitle, $mailBody, new MailAddress($email)))->send();
        } catch (MailException $e) {}
    }
}