<?php

namespace Waboot\inc\core\helpers;

use Waboot\inc\core\AssetsManager;
use Waboot\inc\core\Layout;
use Waboot\inc\core\Theme;

/**
 * @return bool|Theme
 */
function Waboot(): Theme{
    static $waboot = false;
    if(isset($waboot) && $waboot instanceof Theme) return $waboot;
    if(class_exists(Theme::class) && class_exists(Layout::class)){
        $waboot = new Theme(new AssetsManager(), new Layout());
        return $waboot;
    }
    trigger_error('Unable to find Theme class', E_USER_NOTICE);
    return false;
}

/**
 * Returns Theme AssetsManager instance
 *
 * @return bool|AssetsManager
 */
function AssetsManager(): AssetsManager{
    $waboot = \Waboot\inc\core\Waboot();
    if($waboot instanceof Theme){
        return $waboot->getAssetsManager();
    }
    trigger_error('Unable to find Theme class', E_USER_NOTICE);
    return false;
}

/**
 * Returns Theme Layout instance
 *
 * @return bool|Layout
 */
function Layout(): Layout{
    $waboot = Waboot();
    if($waboot instanceof Theme){
        return $waboot->getLayoutHandler();
    }
    trigger_error('Unable to find Theme class', E_USER_NOTICE);
    return false;
}