<?php

namespace Waboot\inc;

/**
 * @return string
 */
function getDefaultLanguage(): string {
    return get_bloginfo('language');
}

/**
 * @return string
 */
function getDefaultTimeZoneName(): string {
    return 'Europe/Rome';
}

/**
 * @return string
 */
function getCurrentLanguage(): string {
    return get_locale();
}

/**
 * @return string
 */
function getCurrentTimeZoneName(): string {
    return 'Europe/Rome';
}