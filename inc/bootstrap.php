<?php

namespace Waboot\inc;

use function Waboot\inc\core\Waboot;

function initWaboot(){
    try{
        $parentPath = get_template_directory();
        require_once $parentPath.'/inc/template-functions.php';
        require_once $parentPath.'/inc/core/template-functions.php';
        Waboot()->loadDependecies();
    }catch (\Exception $e){
        trigger_error($e->getMessage(), E_USER_ERROR);
    }
}

function loadAddons(){
    require_once get_template_directory().'/addons/bootstrap.php';
}