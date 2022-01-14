<?php

namespace Waboot\inc\core;

use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPMailer\PHPMailer\PHPMailer;

class SESMail extends Mail
{
    /**
     * @var string
     */
    private $username;
    /**
     * @var string
     */
    private $password;
    /**
     * @var string
     */
    private $host;

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    public function send(): bool
    {
        try{
            //@see: wp_mail() - pluggable.php
            global $phpmailer;
            // (Re)create it, if it's gone missing.
            if ( ! ( $phpmailer instanceof PHPMailer\PHPMailer\PHPMailer ) ) {
                require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
                require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
                require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
                $phpmailer = new PHPMailer(true);
                $phpmailer::$validator = static function ( $email ) {
                    return (bool) is_email( $email );
                };
            }
            $phpmailer->clearAllRecipients();
            $phpmailer->clearAttachments();
            $phpmailer->clearCustomHeaders();
            $phpmailer->clearReplyTos();
            if($this->getFrom() !== null){
                $phpmailer->setFrom($this->getFrom()->getAddress(),$this->getFrom()->getName() ?? 'WordPress');
            }else{
                $phpmailer->setFrom(get_option('admin_email'),'WordPress');
            }
            $phpmailer->Subject = $this->getSubject();
            $phpmailer->Body = $this->getBody();
            $phpmailer->addAddress($this->getTo()->getAddress(),$this->getTo()->getName() ?? '');
            if(\is_array($this->getCc())){
                foreach ($this->getCc() as $cc){
                    $phpmailer->addCC($cc->getAddress(),$cc->getName() ?? '');
                }
            }
            if(\is_array($this->getBcc())){
                foreach ($this->getBcc() as $bcc){
                    $phpmailer->addBCC($bcc->getAddress(),$bcc->getName() ?? '');
                }
            }
            if($this->isSendingAsHTML()){
                $phpmailer->ContentType = 'text/html';
                $phpmailer->isHTML(true);
            }else{
                $phpmailer->ContentType = 'text/plain';
            }
            $phpmailer->CharSet = 'UTF-8';
            if($this->hasAttachments()){
                foreach ($this->getAttachments() as $attachment){
                    $phpmailer->addAttachment($attachment->getPath());
                }
            }
            if($this->hasHeaders()){
                foreach($this->getHeaders() as $header){
                    $phpmailer->addCustomHeader($header->getName(),$header->getValue());
                }
            }
            $phpmailer->isMail();
            /*
             * Config for SES
             * @see: https://docs.aws.amazon.com/ses/latest/DeveloperGuide/send-using-smtp-php.html
             */
            $phpmailer->isSMTP();
            $phpmailer->Username = $this->getUsername();
            $phpmailer->Password = $this->getPassword();
            $phpmailer->Host = $this->getHost();
            $phpmailer->Port = 587;
            $phpmailer->SMTPAuth = true;
            $phpmailer->SMTPSecure = 'tls';
            return $phpmailer->send();
        }catch (\PHPMailer\PHPMailer\Exception | \Exception $e){
            try{
                $this->initLogger();
                $message = $e instanceof \PHPMailer\PHPMailer\Exception ? $e->errorMessage() : $phpmailer->ErrorInfo;
                $this->getLogger()->error($message);
                return false;
            }catch (LoggerFactoryException $e){
                return false;
            }
        }
    }
}