<?php

namespace Waboot\inc\core;

use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Mail
{
    /**
     * @var string
     */
    protected $subject;
    /**
     * @var string
     */
    protected $body;
    /**
     * @var MailAddress[]
     */
    protected $from;
    /**
     * @var MailAddress[]
     */
    protected $to;
    /**
     * @var MailAddress[]
     */
    protected $cc;
    /**
     * @var MailAddress[]
     */
    protected $bcc;
    /**
     * @var MailHeader[]
     */
    protected $headers;
    /**
     * @var MailAttachment[]
     */
    protected $attachments;
    /**
     * @var bool
     */
    protected $sendAsHTML = false;
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var string
     */
    protected $logDirName = 'waboot-mail';
    /**
     * @var string
     */
    protected $logFileName = 'waboot-mail';

    /**
     * @param string $subject
     * @param string $body
     * @param MailAddress $to
     */
    public function __construct(string $subject, string $body, MailAddress $to)
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->to = $to;
    }

    /**
     * @return string
     */
    public function getSubject(): ?string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * @return string
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    /**
     * @return MailAddress
     */
    public function getFrom(): ?MailAddress
    {
        return $this->from;
    }

    /**
     * @param string $from
     */
    public function setFrom(string $from): void
    {
        $this->from = $from;
    }

    /**
     * @return MailAddress
     */
    public function getTo(): ?MailAddress
    {
        return $this->to;
    }

    /**
     * @param string $to
     */
    public function setTo(string $to): void
    {
        $this->to = $to;
    }

    /**
     * @return MailAddress[]
     */
    public function getCc(): ?array
    {
        return $this->cc;
    }

    /**
     * @param array $cc
     */
    public function setCc(array $cc): void
    {
        $cc = array_filter($cc, static function($cc){
            return $cc instanceof MailAddress;
        });
        if(\is_array($cc) && count($cc) !== 0){
            $this->cc = $cc;
        }
    }

    /**
     * @return MailAddress[]
     */
    public function getBcc(): ?array
    {
        return $this->bcc;
    }

    /**
     * @param array $bcc
     */
    public function setBcc(array $bcc): void
    {
        $bcc = array_filter($bcc, static function($bcc){
            return $bcc instanceof MailAddress;
        });
        if(\is_array($bcc) && count($bcc) !== 0){
            $this->bcc = $bcc;
        }
    }

    /**
     * @return MailAttachment[]
     */
    public function getAttachments(): ?array
    {
        return $this->attachments;
    }

    /**
     * @param MailAttachment $attachment
     */
    public function addAttachment(MailAttachment $attachment): void
    {
        if(!isset($this->attachments)){
            $this->attachments = [];
        }
        $this->attachments[] = $attachment;
    }

    /**
     * @return bool
     */
    public function hasAttachments(): bool
    {
        return \is_array($this->getAttachments()) && count($this->getAttachments()) > 0;
    }

    /**
     * @param MailAttachment[] $attachments
     */
    public function setAttachments(array $attachments): void
    {
        $attachments = array_filter($attachments, static function($attachment){
            return $attachment instanceof MailAttachment;
        });
        if(\is_array($attachments) && count($attachments) > 0){
            $this->attachments = $attachments;
        }
    }

    /**
     * @return bool
     */
    public function isSendingAsHTML(): bool
    {
        return $this->sendAsHTML;
    }

    /**
     * @return void
     */
    public function sendAsHTML(): void
    {
        $this->setSendAsHTML(true);
    }

    /**
     * @return void
     */
    public function sendAsText(): void
    {
        $this->setSendAsHTML(false);
    }

    /**
     * @param bool $sendAsHTML
     */
    public function setSendAsHTML(bool $sendAsHTML): void
    {
        $this->sendAsHTML = $sendAsHTML;
    }

    /**
     * @return MailHeader[]
     */
    public function getHeaders(): ?array
    {
        return $this->headers;
    }

    /**
     * @return bool
     */
    public function hasHeaders(): bool
    {
        return \is_array($this->getHeaders()) && count($this->getHeaders()) > 0;
    }

    /**
     * @param MailHeader[] $headers
     */
    public function setHeaders(array $headers): void
    {
        $headers = array_filter($headers, static function($header){
            return $header instanceof MailHeader;
        });
        if(\is_array($headers) && count($headers) > 0){
            $this->headers = $headers;
        }
    }

    /**
     * @param MailHeader $header
     */
    public function addHeader(MailHeader $header): void
    {
        if(!isset($this->headers)){
            $this->headers = [];
        }
        $this->headers[] = $header;
    }

    /**
     * @return bool
     */
    public function send(): bool
    {
        add_action('wp_mail_failed', function(\WP_Error $e){
            try{
                $this->initLogger();
                $this->getLogger()->error($e->get_error_message());
            }catch (LoggerFactoryException $e){}
        }, 99, 1);

        /*
         * Setup headers
         */
        $headers = '';
        if($this->getFrom() !== null){
            $this->addHeader(new MailHeader('From', $this->getFrom()->getAddress()));
        }
        if(\is_array($this->getCc())){
            foreach ($this->getCc() as $cc){
                $this->addHeader(new MailHeader('Cc', $cc->getAddress()));
            }
        }
        if(\is_array($this->getBcc())){
            foreach ($this->getBcc() as $bcc){
                $this->addHeader(new MailHeader('Bcc', $bcc->getAddress()));
            }
        }
        if($this->isSendingAsHTML()){
            $this->addHeader(new MailHeader('Content-Type', 'text/html; charset=UTF-8'));
        }
        if($this->hasHeaders()){
            $headers = [];
            foreach($this->getHeaders() as $header){
                $headers[] = $header->getName().': '.$header->getValue();
            }
        }

        /*
         * Setup attachments
         */
        $attachments = [];
        if($this->hasAttachments()){
            foreach ($this->getAttachments() as $attachment){
                $attachments[] = $attachment->getPath();
            }
        }

        $body = $this->getBody();
        if($this->isSendingAsHTML()){
            $body = nl2br($body);
        }

        return wp_mail($this->getTo()->getAddress(),$this->getSubject(),$body,$headers,$attachments);
    }

    /**
     * @throws LoggerFactoryException
     */
    protected function initLogger(): void
    {
        $logger = LoggerFactory::create('waboot-mail-logger',$this->getLogFile());
        $this->logger = $logger;
    }

    /**
     * @return bool
     */
    protected function hasLogger(): bool
    {
        return $this->getLogger() instanceof Logger;
    }

    /**
     * @return Logger
     */
    protected function getLogger(): ?Logger
    {
        return $this->logger ?? null;
    }

    /**
     * @return string
     */
    protected function getLogsDir(): string
    {
        return WP_CONTENT_DIR.'/mail-logs/'.$this->logDirName;
    }

    /**
     * @return string
     */
    protected function getLogFile(): string
    {
        static $logFile;
        if(isset($logFile)){
            return $logFile;
        }
        $logFile = $this->getLogsDir().'/'.$this->logFileName.'-'.(new \DateTime())->format('Y-m-d').'.log';
        return $logFile;
    }
}