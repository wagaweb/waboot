<?php

namespace Waboot\inc\core\utils;

class Dates
{
    /**
     * @param \DateTimeZone|null $tz
     * @return \DateTime
     * @throws \Exception
     */
    public static function getToday(\DateTimeZone $tz = null): \DateTime
    {
        if($tz === null){
            $tz = self::getDefaultDateTimeZone();
        }
        return new \DateTime('now', $tz);
    }

    /**
     * @param string $timezone
     * @return bool
     */
    public static function isValidTimezone(string $timezone): bool
    {
        return \in_array($timezone, \timezone_identifiers_list(),true);
    }

    /**
     * @param string $timezone
     * @return \DateTimeZone
     */
    public static function getDateTimeZoneFromString(string $timezone): \DateTimeZone
    {
        if(!self::isValidTimezone($timezone)){
            return self::getDefaultDateTimeZone();
        }
        return new \DateTimeZone($timezone);
    }

    /**
     * @return \DateTimeZone
     */
    public static function getDefaultDateTimeZone(): \DateTimeZone
    {
        return new \DateTimeZone(date_default_timezone_get() ?: 'UTC');
    }
}