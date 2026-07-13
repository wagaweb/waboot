<?php

namespace Waboot\addons\packages\clear_opcache;

add_action('admin_bar_menu', static function($wp_admin_bar){
    if(!current_user_can( 'manage_options' ) ) {
        return;
    }
    $args = array(
        'id' => 'clear_opcache',
        'title' => 'Clear OPCACHE',
        'href' => esc_url(admin_url('index.php?action=clear_opcache'))
    );
    $wp_admin_bar->add_node($args);
}, 1000);

add_action('admin_init', static function(){
    if(!isset($_GET['action'])){
        return;
    }
    if($_GET['action'] !== 'clear_opcache'){
        return;
    }
    //Clear the opcache
    if(function_exists('opcache_reset')){
        $r = opcache_reset();
        if($r){
            add_action('admin_notices', static function(){
                $class = 'notice notice-success is-dismissible';
                $message = 'OPCache cleared successfully';
                printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
            });
        }else{
            add_action('admin_notices', static function(){
                $class = 'notice notice-error is-dismissible';
                $message = 'OPCache not cleared';
                printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
            });
        }
    }else{
        add_action('admin_notices', static function(){
            $class = 'notice notice-error is-dismissible';
            $message = 'OPCache does not exists';
            printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
        });
    }
});