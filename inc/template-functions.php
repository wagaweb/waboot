<?php

namespace Waboot\functions;
use WBF\includes\mvc\HTMLView;

/**
 * Wrapper for \WBF\modules\options\of_get_option
 *
 * @param $name
 * @param bool $default
 *
 * @return bool|mixed
 */
function get_option($name, $default = false){
	if(class_exists("WBF")){
		return \WBF\modules\options\of_get_option($name,$default);
	}else{
		return $default;
	}
}

/**
 * Checks if at least one widget area with $prefix is active (eg: footer-1, footer-2, footer-3...)
 *
 * @param $prefix
 *
 * @param int $limit (default: 4)
 *
 * @return bool
 */
function count_widgets_in_area($prefix,$limit = 4){
	$count = 0;
	for($i = 1; $i <= $limit; $i++) {
		if(is_active_sidebar($prefix . "-" . $i)) {
			$count++;
		}
	}
	return $count;
}

/**
 * Prints out a waboot-type widget area
 *
 * @param $prefix
 * @param int $limit
 */
function print_widgets_in_area($prefix,$limit = 4){
	$count = count_widgets_in_area($prefix,$limit);
	if($count === 0) return;
	$sidebar_class = get_grid_class_for_alignment($count);
	(new HTMLView("templates/widget-area.php"))->clean()->display([
		'widget_area_prefix' => $prefix,
		'widget_count' => $count,
		'sidebar_class' => $sidebar_class
	]);
}

/**
 * Get the correct CSS class to align $count containers
 *
 * @param int $count
 *
 * @return string
 *
 */
function get_grid_class_for_alignment($count = 4){
	$class = '';
	$count = intval($count);
	switch($count) {
		case 1:
			$class = 'col-sm-12';
			break;
		case 2:
			$class = 'col-sm-6';
			break;
		case 3:
			$class = 'col-sm-4';
			break;
		case 4:
			$class = 'col-sm-3';
			break;
		default:
			$class = 'col-sm-1';
	}
	$class = apply_filters("waboot/layout/grid_class_for_alignment",$class,$count);
	return $class;
}

/**
 * Gets theme widget areas
 *
 * @return array
 */
function get_widget_areas(){
	$areas = [
		'sidebar-1' => [
			'name' =>  __('Secondary Sidebar', 'waboot'),
			'description' => __( 'The main widget area displayed in the sidebar.', 'waboot' )
		],
		'sidebar-2' => [
			'name' => __('Secondary Sidebar', 'waboot'),
			'description' => __( 'The main widget area displayed in the sidebar.', 'waboot' )
		],
		'footer-1' => [
			'name' => __('Footer 1', 'waboot'),
			'description' => __('The footer widget area displayed after all content.', 'waboot' )
		],
		'footer-2' => [
			'name' => __('Footer 2', 'waboot'),
			'description' => __('The second footer widget area, displayed below the Footer widget area.', 'waboot' )
		],
		'footer-3' => [
			'name' => __('Footer 3', 'waboot'),
			'description' => __('The third footer widget area, displayed below the Footer widget area.', 'waboot' )
		],
		'footer-4' => [
			'name' => __('Footer 4', 'waboot'),
			'description' => __('The fourth footer widget area, displayed below the Footer widget area.', 'waboot' )
		],
		'topbar' => [
			'name' => __('Top Bar', 'waboot'),
		],
		'banner' => [
			'name' => __('Banner', 'waboot')
		],
		'contentbottom' => [
			'name' => __('Content Bottom')
		],
		'header-left' => [
			'name' => __('Header Left')
		],
		'header-right' => [
			'name' => __('Header Right')
		],
	];

	$areas = apply_filters("waboot/widget_areas",$areas);

	return $areas;
}

/**
 * Get available socials
 * 
 * @return mixed
 */
function get_available_socials(){
	$socials = apply_filters("waboot/socials/available",[
		'facebook' => [
			'name' => __( 'Facebook', 'waboot' ),
			'theme_options_desc' => __( 'Enter your facebook fan page link', 'waboot' ),
			'icon_class' => 'fa-facebook'
		],
		'twitter'  => [
			'name' => __( 'Twitter', 'waboot' ),
			'theme_options_desc' => __( 'Enter your twitter page link', 'waboot' ),
			'icon_class' => 'fa-twitter'
		],
		'google'  => [
			'name' => __( 'Google+', 'waboot' ),
			'theme_options_desc' => __( 'Enter your google+ page link', 'waboot' ),
			'icon_class' => 'fa-google-plus'
		],
		'youtube'  => [
			'name' => __( 'YouTube', 'waboot' ),
			'theme_options_desc' => __( 'Enter your youtube page link', 'waboot' ),
			'icon_class' => 'fa-youtube'
		],
		'pinterest'  => [
			'name' => __( 'Pinterest', 'waboot' ),
			'theme_options_desc' => __( 'Enter your pinterest page link', 'waboot' ),
			'icon_class' => 'fa-pinterest'
		],
		'linkedin'  => [
			'name' => __( 'Linkedin', 'waboot' ),
			'theme_options_desc' => __( 'Enter your linkedin page link', 'waboot' ),
			'icon_class' => 'fa-linkedin'
		],
		'instagram'  => [
			'name' => __( 'Instagram', 'waboot' ),
			'theme_options_desc' => __( 'Enter your instagram page link', 'waboot' ),
			'icon_class' => 'fa-instagram'
		],
		'feedrss'  => [
			'name' => __( 'Feed RSS', 'waboot' ),
			'theme_options_desc' => __( 'Enter your feed RSS link', 'waboot' ),
			'icon_class' => 'fa-rss'
		]
	]);
	return $socials;
}

/**
 * Returns the appropriate title for the archive page
 *
 * @return string
 */
function get_archive_page_title(){
	global $post;
	if ( is_category() ) {
		return single_cat_title('',false);
	} elseif ( is_tag() ) {
		return single_tag_title('',false);
	} elseif ( is_author() ) {
		$author_name = get_the_author_meta("display_name",$post->post_author);
		return sprintf( __( 'Author: %s', 'waboot' ), '<span class="vcard"><a class="url fn n" href="' . get_author_posts_url( $post->post_author ) . '" title="' . esc_attr( $author_name ) . '" rel="me">' . $author_name . '</a></span>' );
	} elseif ( is_day() ) {
		return sprintf( __( 'Day: %s', 'waboot' ), '<span>' . get_the_date('', $post->ID) . '</span>' );
	} elseif ( is_month() ) {
		return sprintf( __( 'Month: %s', 'waboot' ), '<span>' . get_the_date('F Y', $post->ID ) . '</span>' );
	} elseif ( is_year() ) {
		return printf( __( 'Year: %s', 'waboot' ), '<span>' . get_the_date('Y', $post->ID ) . '</span>' );
	} elseif ( is_tax( 'post_format', 'post-format-aside' ) ) {
		return __( 'Asides', 'waboot' );
	} elseif ( is_tax( 'post_format', 'post-format-gallery' ) ) {
		return __( 'Galleries', 'waboot');
	} elseif ( is_tax( 'post_format', 'post-format-image' ) ) {
		return __( 'Images', 'waboot');
	} elseif ( is_tax( 'post_format', 'post-format-video' ) ) {
		return __( 'Videos', 'waboot' );
	} elseif ( is_tax( 'post_format', 'post-format-quote' ) ) {
		return __( 'Quotes', 'waboot' );
	} elseif ( is_tax( 'post_format', 'post-format-link' ) ) {
		return __( 'Links', 'waboot' );
	} elseif ( is_tax( 'post_format', 'post-format-status' ) ) {
		return __( 'Statuses', 'waboot' );
	} elseif ( is_tax( 'post_format', 'post-format-audio' ) ) {
		return __( 'Audios', 'waboot' );
	} elseif ( is_tax( 'post_format', 'post-format-chat' ) ) {
		return __( 'Chats', 'waboot' );
	} else {
		$arch_obj = get_queried_object();
		if(isset($arch_obj->name))
			return $arch_obj->name;
		return __( 'Archives', 'waboot' );
	}
}

/**
 * Return the current blog layout or the default one ("classic")
 * @return bool|string
 */
function get_blog_layout(){
	$blog_style = \Waboot\functions\get_option("blogpage_layout");
	if (!$blog_style || $blog_style == "") $blog_style = "classic";

	return $blog_style;
}

/**
 * Return the class relative to the $blog_layout (by default the current blog layout)
 *
 * @param bool $blog_layout
 * @return mixed
 */
function get_blog_class($blog_layout = false){
	if(!$blog_layout){
		$blog_layout = get_blog_layout();
	}

	$classes = array(
		"blog-".$blog_layout
	);

	if($blog_layout == "masonry"){
		$classes[] = "row";
	}

	return implode(" ",$classes);
}