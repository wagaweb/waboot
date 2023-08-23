<?php

namespace Waboot\addons\packages\checkout;

use Waboot\inc\core\utils\WordPress;

WordPress::addAjaxEndpoint('is_customer_logged_in', static function(){
    wp_send_json_success([
        'is_logged_in' => is_user_logged_in()
    ]);
});

WordPress::addAjaxEndpoint('is_email_registered', static function(){
    $email = $_POST['email'] ?? false;
    if(!$email){
        wp_send_json_error(['error' => 'invalid_email']);
    }
    $email = sanitize_email($email);
    $user = get_user_by('email',$email);
    if($user instanceof \WP_User){
        wp_send_json_success([
            'is_email_registered' => true
        ]);
    }else{
        wp_send_json_success([
            'is_email_registered' => false
        ]);
    }
});