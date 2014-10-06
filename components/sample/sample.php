<?php
/**
Component Name: Sample
Description: Sample component
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
*/

class FullpageComponent extends Waboot_Component{

    public function setup(){
        parent::setup();
        //Do stuff...
    }

    public function scripts(){
        //Enqueue scripts
    }

    public function styles(){
        //Enqueue styles
    }

    public function theme_options($options){
        $options = parent::theme_options($options);
        //Do stuff...
        return $options;
    }

    public function onActivate(){
        parent::onActivate();
        //Do stuff...
    }

    public function onDeactivate(){
        parent::onDeactivate();
        //Do stuff...
    }
}