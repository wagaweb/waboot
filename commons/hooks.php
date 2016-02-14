<?php

if(!function_exists("wbft_header_flush")):
	/**
	 * Flush(); after the header: https://developer.yahoo.com/performance/rules.html
	 */
	function wbft_header_flush(){
		flush();
	}
	add_action( 'wp_head', 'wbft_header_flush', 9999 );
endif;

