<?php

require_once("core/functions.php");
require_once("inc/core_customization.php");

/*-----------------------------------------------------------------------------------*/
/* Start Custom Functions - Please refrain from editing this section */
/*-----------------------------------------------------------------------------------*/


// Register our sidebars and widgetized areas
function arphabet_widgets_init() {

	register_sidebar( array(
		'name' => 'Banner',
		'id' => 'banner',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h2 class="rounded">',
		'after_title' => '</h2>',
	) );
	register_sidebar( array(
		'name' => 'Content Bottom',
		'id' => 'contentbottom',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h2 class="rounded">',
		'after_title' => '</h2>',
	) );
	register_sidebar( array(
		'name' => 'Header Left',
		'id' => 'header-left',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h2 class="rounded">',
		'after_title' => '</h2>',
	) );
	register_sidebar( array(
		'name' => 'Header Right',
		'id' => 'header-right',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h2 class="rounded">',
		'after_title' => '</h2>',
	) );
}
add_action( 'widgets_init', 'arphabet_widgets_init' );


// Add WP Better email support for gravity form
add_filter('gform_notification', 'change_notification_format', 10, 3);
function change_notification_format( $notification, $form, $entry ) {

	// is_plugin_active is not availble on front end
	if( !is_admin() )
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	// does WP Better Emails exists and activated ?
	if( !is_plugin_active('wp-better-emails/wpbe.php') )
		return $notification;

	// change notification format to text from the default html
    $notification['message_format'] = "text";
	// disable auto formatting so you don't get double line breaks
	$notification['disableAutoformat'] = true;

    return $notification;
}


/*-----------------------------------------------------------------------------------*/
/* Don't add any code below here or the sky will fall down */
/*-----------------------------------------------------------------------------------*/
?>