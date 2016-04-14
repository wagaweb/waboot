<?php

namespace Waboot\hooks;

/**
 * Add header metas
 */
function add_header_metas(){
	get_template_part("templates/parts/meta");
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
 * Adds breadcrumb
 */
function add_breadcrumb(){
	if(function_exists('is_woocommerce') && is_woocommerce()){ //@woocommerce hard-coded integration
		woocommerce_breadcrumb([
			'wrap_before'   => '<div class="breadcrumb-trail breadcrumbs" itemprop="breadcrumb"><div class="container">',
			'wrap_after'   => '</div></div>',
			'delimiter'  => '<span class="sep">&nbsp;&#47;&nbsp;</span>'
		]);
	}else{
		global $post;
		if(!isset($post)) return;
		\Waboot\template_tags\breadcrumb($post->ID, 'before_inner', [
			'wrapper_start' => '<div class="container">', 
			'wrapper_end' => '</div>'
		]);
	}
}
add_action("waboot/main/before",__NAMESPACE__."\\add_breadcrumb");