<?php

namespace WBF\modules\pagebuilder;

define("PAGEBUILDERS_PATH",get_template_directory()."/pagebuilders/");

//require_once(WBF_DIRECTORY."/vendor/mgargano/simplehtmldom/src/simple_html_dom.php");
require_once("functions.php");

//Get block AJAX
add_action("wp_ajax_pagebuilder_get_block",'\WBF\modules\pagebuilder\Manager::get_block');
add_action("wp_ajax_no_priv_pagebuilder_get_block",'\WBF\modules\pagebuilder\Manager::get_block');
//Get block edit screen AJAX
add_action( "wp_ajax_pagebuilder_get_edit_screen", '\WBF\modules\pagebuilder\Manager::get_block_edit_screen' );
add_action( "wp_ajax_no_priv_pagebuilder_get_edit_screen", '\WBF\modules\pagebuilder\Manager::get_block_edit_screen' );
//Compiling AJAX
add_action( "wp_ajax_pagebuilder_compile", '\WBF\modules\pagebuilder\Manager::async_compile_pb_content' );
add_action( "wp_ajax_no_priv_pagebuilder_compile", '\WBF\modules\pagebuilder\Manager::async_compile_pb_content' );
add_action( "wp_ajax_pagebuilder_getRaw", '\WBF\modules\pagebuilder\Manager::async_decompile_pb_content' );
add_action( "wp_ajax_no_priv_pagebuilder_getRaw", '\WBF\modules\pagebuilder\Manager::async_decompile_pb_content' );
//JSON Decode
add_action( "wp_ajax_parseJSON", '\WBF\modules\pagebuilder\wbpb_json_decode' );
add_action( "wp_ajax_no_priv_parseJSON", '\WBF\modules\pagebuilder\wbpb_json_decode' );
add_action( "wp_ajax_JSON_encode", '\WBF\modules\pagebuilder\wbpb_json_encode' );
add_action( "wp_ajax_no_priv_JSON_encode", '\WBF\modules\pagebuilder\wbpb_json_encode' );
//Create Preview
add_action( "wp_ajax_create_excerpt", '\WBF\modules\pagebuilder\PageBuilderTools::create_excerpt' );
add_action( "wp_ajax_no_priv_create_excerpt", '\WBF\modules\pagebuilder\PageBuilderTools::create_excerpt' );

set_current_builder();

add_action( 'admin_init', '\WBF\modules\pagebuilder\init' );

add_action('wbf_init', '\WBF\modules\pagebuilder\init_shortcodes');
function init_shortcodes(){
	if(get_current_builder()){
		Manager::init_shortcodes();
	}
}