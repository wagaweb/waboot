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
 * @param string $to
 * @param array $customHeaders
 * @param array $attachments
 * @param bool $sendAsHtml
 * @return bool
 * @throws MailAttachmentException
 * @throws MailException
 */
function sendMail(string $subject, string $body, string $to, array $customHeaders = [], array $attachments = [], bool $sendAsHtml = true): bool {
    $m = new Mail($subject,$body,new MailAddress($to));
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