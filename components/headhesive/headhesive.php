<?php
/**
Component Name: Headhesive
Description: Headhesive component
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
 */

class HeadhesiveComponent extends Waboot_Component{
    public function scripts(){
        wp_enqueue_script(
            'headhesive-script',
            $this->directory_uri . '/headhesive.js',
            array('jquery'),
            '1.0.0',
            'false'
        );
        wp_enqueue_script(
            'custom-headhesive-script',
            $this->directory_uri . '/headhesive-custom.js',
            array('jquery'),
            '1.0.0',
            'false'
        );
    }

    public function styles(){
        //Enqueue styles
        wp_enqueue_style(
            'headhesive-style',
            $this->directory_uri . '/headhesive.css'
        );
    }
}