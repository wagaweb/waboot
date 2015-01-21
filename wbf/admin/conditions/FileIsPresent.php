<?php

namespace WBF\admin\conditions;

class FileIsPresent implements Condition{
    var $file;

    function __construct($file){
        $this->file = $file;
    }

    function verify(){
        if(is_array($this->file)){
            foreach($this->file as $f){
                if(is_file($f)){
                    return true;
                }
            }
        }else{
            if(is_file($this->file)){
                return true;
            }
        }
        return false;
    }
} 