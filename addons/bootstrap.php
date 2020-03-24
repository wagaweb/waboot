<?php

namespace Waboot\addons;

require_once 'functions.php';
require_once 'shared-functions.php';
require_once 'shared-hooks.php';

foreach (getAddons() as $addonName){
    $btf = getAddonDirectory($addonName).'/bootstrap.php';
    if(is_file($btf)){
        require_once $btf;
    }
}