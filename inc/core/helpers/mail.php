<?php

namespace Waboot\inc\core\helpers;

use Waboot\inc\core\Mail;
use Waboot\inc\core\MailAddress;
use Waboot\inc\core\MailAttachment;
use Waboot\inc\core\MailAttachmentException;
use Waboot\inc\core\MailException;
use Waboot\inc\core\MailHeader;

/**
 * Send a mail
 *
 * $customHeaders is an array of arrays, each inner array must have 'name' and 'value'
 * $attachments is an array of arrays, each inner array must have 'name' and 'path'
 *
 * @param string $subject
 * @param string $body
 * @param string|array $to
 * @param array $customHeaders
 * @param array $attachments
 * @param bool $sendAsHtml
 * @return bool
 * @throws MailAttachmentException
 * @throws MailException
 */
function sendMail(string $subject, string $body, $to, array $customHeaders = [], array $attachments = [], bool $sendAsHtml = true): bool {
    if(\is_array($to)){
        $to = array_map(fn($to) => new MailAddress($to),$to);
    }else{
        $to = new MailAddress($to);
    }
    $m = new Mail($subject,$body,$to);
    if(!empty($customHeaders)){
        foreach ($customHeaders as $customHeaderData){
            if(!array_key_exists('name',$customHeaderData)){
                continue;
            }
            if(!array_key_exists('value',$customHeaderData)){
                continue;
            }
            $h = new MailHeader($customHeaderData['name'],$customHeaderData['value']);
            $m->addHeader($h);
        }
    }
    if(!empty($attachments)){
        foreach ($attachments as $attachmentData){
            if(!array_key_exists('name',$attachmentData)){
                continue;
            }
            if(!array_key_exists('path',$attachmentData)){
                continue;
            }
            $a = new MailAttachment($attachmentData['name'],$attachmentData['path']);
            $m->addAttachment($a);
        }
    }
    $m->setSendAsHTML($sendAsHtml);
    return $m->send();
}