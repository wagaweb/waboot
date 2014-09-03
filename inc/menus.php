<?php
/**
 * Register the navigation menus. This theme uses wp_nav_menu() in three locations.
 */
register_nav_menus( array(
	'top'           => __( 'Top Menu', 'waboot' ),
	'main'          => __( 'Main Menu', 'waboot' ),
	'bottom'        => __( 'Bottom Menu', 'waboot' )
) );
