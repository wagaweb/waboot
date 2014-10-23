<?php

if ( ! class_exists( "FirePHP" ) ) {
	require_once "vendor/firephp/FirePHP.class.php";
}
waboot_debug_init();

function waboot_debug_init() {
	$GLOBALS['wbdebug'] = FirePHP::getInstance( true );
	if ( WABOOT_ENV == "dev" && current_user_can( "administrator" ) ) {
		$GLOBALS['wbdebug']->setEnabled( true );
		$GLOBALS['wbdebug']->registerErrorHandler();
	} else {
		$GLOBALS['wbdebug']->setEnabled( false );
	}
}

function waboot_dumb( $var, $label = "", $action = "log" ) {
	global $wbdebug;
	if ( empty( $label ) ) {
		$wbdebug->$action( $var );
	} else {
		$wbdebug->$action( $var, $label );
	}
}

function waboot_dump_groupStart( $label ) {
	global $wbdebug;
	$wbdebug->group( $label );
}

function waboot_dump_groupEnd( $label ) {
	global $wbdebug;
	$wbdebug->groupEnd();
}