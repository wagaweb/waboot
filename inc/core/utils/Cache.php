<?php

namespace Waboot\inc\core\utils;

class Cache
{
    const TRANSIENT_PREFIX = 'wb';

    /**
     * @param string $transientName
     * @param $value
     * @param float|int $expire
     * @return bool
     */
    public static function setTransient(string $transientName, $value, int $expire = DAY_IN_SECONDS): bool
    {
        if(!self::canSetTransient()){
            return false;
        }
        if(strpos($transientName, self::TRANSIENT_PREFIX) !== 0){
            $transientName = self::TRANSIENT_PREFIX.$transientName;
        }
        return set_transient($transientName, $value, $expire);
    }

    /**
     * @param string $transientName
     * @return bool|mixed
     */
    public static function getTransient(string $transientName)
    {
        if(!self::canSetTransient()){
            return false;
        }
        if(strpos($transientName, self::TRANSIENT_PREFIX) !== 0){
            $transientName = self::TRANSIENT_PREFIX.$transientName;
        }
        return get_transient($transientName);
    }

    /**
     * @return bool
     */
    public static function canSetTransient(): bool
    {
        if(defined('WB_FORCE_TRANSIENT') && WB_FORCE_TRANSIENT){
            return true;
        }
        if(defined('WP_DEBUG') && WP_DEBUG){
            return false;
        }
        return true;
    }
}
