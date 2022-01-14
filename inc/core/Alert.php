<?php

namespace Waboot\inc\core;

use Waboot\inc\core\utils\Dates;

class Alert
{
    /**
     * @var string
     */
    protected $id;
    /**
     * @var string
     */
    protected $title;
    /**
     * @var string
     */
    protected $message;
    /**
     * @var \DateTimeZone
     */
    protected $timeZone;
    /**
     * @var \DateTime
     */
    protected $dateTime;

    /**
     * @param string $id
     * @param string $title
     * @param string $message
     * @param string|null $tz
     * @throws AlertException
     */
    public function __construct(string $id, string $title, string $message, string $tz = null)
    {
        $this->id = $id;
        $this->title = $title;
        $this->message = $message;
        try{
            if(isset($tz)){
                $timeZone = Dates::getDateTimeZoneFromString($tz);
            }else{
                $timeZone = Dates::getDefaultDateTimeZone();
            }
            $this->timeZone = $timeZone;
            $this->dateTime = new \DateTime('now', $this->timeZone);
        }catch (\Exception $e){
            throw new AlertException($e->getMessage());
        }
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @param string $format
     * @return string
     */
    public function getTimeStamp(string $format = 'Y-m-d H:i:s'): string
    {
        return $this->dateTime->format($format);
    }
}