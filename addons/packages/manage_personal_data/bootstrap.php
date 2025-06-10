<?php

namespace Waboot\addons\packages\manage_personal_data;

add_action('woocommerce_init', static function () {
    if(!is_user_logged_in()) {
        return;
    }
    $currentEndpoint = strtok($_SERVER['REQUEST_URI'], '?');
    if(!str_contains(wc_get_account_endpoint_url('edit-account'),$currentEndpoint)) {
        return;
    }
    $actionType = $_GET['manage-my-data'] ?? null;
    if(!$actionType) {
        return;
    }
    $actionType = sanitize_text_field(wp_unslash($actionType));
    $emailAddress = wp_get_current_user()->user_email;
    $status = 'confirmed';
    $requestId = wp_create_user_request($emailAddress, $actionType, [], $status);
    if(is_wp_error($requestId)) {
        wc_add_notice(sprintf(
            _x('An error occured processing your request: %s','manage_personal_data', LANG_TEXTDOMAIN),
            $requestId->get_error_message()
        ), 'error');
    }else{
        wc_add_notice(
            _x('Your request has been inserted successfully. You will get notified when it gets processed.','manage_personal_data', LANG_TEXTDOMAIN),
            'success'
        );
    }
});

add_action('woocommerce_after_edit_account_form', static function () {
    $eraseDataUrl = add_query_arg(['manage-my-data' => 'remove_personal_data'],wc_get_account_endpoint_url('edit-account'));
    $exportDataUrl = add_query_arg(['manage-my-data' => 'export_personal_data'],wc_get_account_endpoint_url('edit-account'));
    ?>
    <style>
        .manage-personal-data-actions--list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: 20px;
            justify-content: flex-end;
        }

        .manage-personal-data-actions--list li a {
            text-decoration: none;
            color: #333;
            font-size: 16px;
            font-weight: 500;
            padding: 5px 0;
            border-bottom: 2px solid transparent;
            transition: color 0.3s ease, border-color 0.3s ease;
        }

        .manage-personal-data-actions--list li a:hover {
            color: #000000;
            border-bottom-color: #000000;
        }
    </style>
    <ul class="manage-personal-data-actions--list">
        <li><a href="<?php echo $eraseDataUrl; ?>"><?php _ex('Erase your data', 'manage_personal_data', LANG_TEXTDOMAIN) ?></a></li>
        <li><a href="<?php echo $exportDataUrl; ?>"><?php _ex('Export your data', 'manage_personal_data', LANG_TEXTDOMAIN) ?></a></li>
    </ul>
    <?php
});