<?php

namespace Waboot\inc\core\utils;

class Dates
{
    /**
     * @param string $format
     * @param string $string
     * @param \DateTimeZone|null $tz
     * @return \DateTime|null
     */
    public static function createFromFormat(string $format, string $string, \DateTimeZone $tz = null): ?\DateTime
    {
        if(!$tz){
            $tz = self::getDefaultDateTimeZone();
        }
        $dt = date_create_from_format($format,$string,$tz);
        if(!$dt instanceof \DateTime){
            return null;
        }
        return $dt;
    }

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
        if(function_exists('wc_timezone_string')){
            $tz = wc_timezone_string();
        }else{
            $tz = date_default_timezone_get() ?? 'UTC';
        }
        return new \DateTimeZone($tz);
    }
}