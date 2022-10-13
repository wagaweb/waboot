<?php

namespace Waboot\inc\multilanguage;

/**
 * @return string
 */
function getDefaultLanguage(): string {
    return get_bloginfo('language');
}

/**
 * @return string
 */
function getCurrentLanguage(): string {
    return get_locale();
}