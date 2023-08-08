<?php

namespace Waboot\addons;

require_once __DIR__.'/functions.php';
require_once __DIR__.'/shared-functions.php';
require_once __DIR__.'/shared-hooks.php';

foreach (getAddons() as $addonName){
    $btf = getAddonDirectory($addonName).'/bootstrap.php';
    if(is_file($btf)){
        require_once $btf;
    }
}