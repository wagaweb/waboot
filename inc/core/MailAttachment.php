<?php

namespace Waboot\inc\core;

class MailAttachment
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $path;

    /**
     * @param string $name
     * @param string $path
     * @throws MailAttachmentException
     */
    public function __construct(string $name, string $path)
    {
        $this->name = $name;
        if(!is_file($path)){
            throw new MailAttachmentException('File '.$path.' not found');
        }
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
}