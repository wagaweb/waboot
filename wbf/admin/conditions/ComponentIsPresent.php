<?php
/**
 * Created by PhpStorm.
 * User: wagadev
 * Date: 21/01/15
 * Time: 12.24
 */

namespace WBF\admin\conditions;

class ComponentIsPresent implements Condition {

    var $c_name;

    function __construct($c_name){
        $this->c_name = $c_name;
    }

    function verify(){
        $registered_components = \Waboot_ComponentsManager::getAllComponents();
        if(isset($registered_components[$this->c_name]) && is_file($registered_components[$this->c_name]['file'])){
            return true;
        }

        return false;
    }
} 