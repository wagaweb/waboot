<?php

namespace Waboot\hooks;
use Waboot\LS;
use WBF\components\license\License_Manager;

/**
 * Add header metas
 */
function add_header_metas(){
	get_template_part("templates/wordpress/parts/meta");
}
add_action("waboot/head/start",__NAMESPACE__."\\add_header_metas");

/**
 * Adds apple touch init to the document head meta
 */
function add_apple_touch_icon(){
	?>
	<link rel="apple-touch-icon" href="<?php apply_filters("waboot/assets/apple-touch-icon-path","apple-touch-icon.png"); ?>">
	<?php
}
add_action("waboot/head/meta",__NAMESPACE__."\\add_apple_touch_icon");

/**
 * Adds banner sidebar zone to header
 */
function add_banner_wrapper(){
	if(!is_active_sidebar('banner')) return;
	get_template_part("templates/parts/banner-wrapper");
}
add_action("waboot/header",__NAMESPACE__."\\add_banner_wrapper");

/**
 * Adds Waboot credits
 *
 * @param $text
 *
 * @return mixed
 */
function add_credits($text){
	$our_text = sprintf(__(", and <a href='%s'>Waboot</a>","waboot"),""); //todo: finire
	return $text;
}
add_filter("admin_footer_text",__NAMESPACE__."\\add_credits");

/**
 * Sets the default components
 *
 * @param $default_components
 *
 * @return array
 */
function set_default_components($default_components){
	$default_components = [
		'header_classic',
		'footer_classic',
		'breadcrumb',
		'topNavWrapper',
	];
	return $default_components;
}
add_filter("wbf/modules/components/defaults",__NAMESPACE__."\\set_default_components");

/**
 * Puts some custom post types into blacklist (in these post types the behavior will never be displayed)
 * @param $blacklist
 * @return array
 */
function behaviors_cpts_blacklist($blacklist){
	$blacklist[] = "metaslider";
	return $blacklist;
}
add_filter("wbf/modules/behaviors/post_type_blacklist",__NAMESPACE__."\\behaviors_cpts_blacklist");

/**
 * Ignore sticky posts in archives
 * @param \WP_Query $query
 */
function ignore_sticky_post_in_archives($query){
	if(is_category() || is_tag() || is_tax()) {
		$query->set("post__not_in",get_option( 'sticky_posts', array() ));
	}
}
add_action( 'pre_get_posts', __NAMESPACE__.'\\ignore_sticky_post_in_archives' );

/**
 * Manage WB LS
 */
function rg_ls(){
	require_once("../ls.php");
	License_Manager::register_theme_license(LS::getInstance("waboot",['suffix'=>true]));
}
add_action("wbf_init",__NAMESPACE__."\\rg_ls");