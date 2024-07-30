<?php

namespace Waboot\inc\gravityform;

/**
 * Changes the Gravity Forms notification emails from HTML to plain text.
 *
 * @param $notification
 * @param $form
 * @param $entry
 * @return mixed
 */
add_filter( 'gform_notification', function( $notification, $form, $entry ) {

    // is_plugin_active is not availble on front end
    if( ! is_admin() )
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

    // Check if WPBE is installed and activated
    if( ! is_plugin_active('wp-better-emails/wpbe.php') )
        return $notification;

    // change notification format to text from the default html
    $notification['message_format'] = "text";
    // disable auto formatting so you don't get double line breaks
    $notification['disableAutoformat'] = true;

    return $notification;

}, 10, 3);