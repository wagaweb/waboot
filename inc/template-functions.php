<?php

namespace Waboot\functions;
use Waboot\Layout;
use WBF\components\mvc\HTMLView;
use WBF\components\utils\Utilities;

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
 * Wrapper for \WBF\modules\behaviors\get_behavior
 *
 * @param $name
 * @param $default
 * @param int $post_id
 * @param string $return
 *
 * @return array|bool|mixed|string
 */
function get_behavior($name, $default = "", $post_id = 0, $return = "value"){
	if(class_exists("WBF")){
		$result = \WBF\modules\behaviors\get_behavior($name, $post_id, $return);
		if(!$result){
			return $default;
		}
		return $result;
	}else{
		return $default;
	}
}

/**
 * Checks if at least one widget area with $prefix is active (eg: footer-1, footer-2, footer-3...)
 *
 * @param $prefix
 *
 * @return bool
 */
function count_widgets_in_area($prefix){
	$count = 0;
	$areas = get_widget_areas();
	if(isset($areas[$prefix]) || !isset($areas[$prefix]['type']) || $areas[$prefix]['type'] != "multiple"){
		$limit = isset($areas[$prefix]['subareas']) && intval($areas[$prefix]['subareas']) > 0 ? $areas[$prefix]['subareas'] : 0;
		for($i = 1; $i <= $limit; $i++) {
			if(is_active_sidebar($prefix . "-" . $i)) {
				$count++;
			}
		}
	}
	return $count;
}

/**
 * Prints out a waboot-type widget area
 *
 * @param $prefix
 */
function print_widgets_in_area($prefix){
	$count = count_widgets_in_area($prefix);
	if($count === 0) return;
	$sidebar_class = get_grid_class_for_alignment($count);
	(new HTMLView("templates/widget_areas/multi-widget-area.php"))->clean()->display([
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
		'header' => [
			'name' =>  __('Header', 'waboot'),
			'description' => __( 'The main widget area displayed in the header.', 'waboot' ),
			'render_zone' => 'header'
		],
		'main_top' => [
			'name' => __('Main Top', 'waboot'),
			'description' => __( 'Widget area displayed above the content and the sidebars.', 'waboot' ),
			'render_zone' => 'main-top'
		],
		'sidebar_left' => [
			'name' => __('Sidebar left', 'waboot'),
			'description' => __('Widget area displayed in left aside', 'waboot' ),
			'render_zone' => 'aside-primary'
		],
		'content_top' => [
			'name' => __('Content Top', 'waboot'),
			'description' => __('Widget area displayed above the content', 'waboot' ),
			'render_zone' => 'content',
			'render_priority' => 10
		],
		'content_bottom' => [
			'name' => __('Content Bottom', 'waboot'),
			'description' => __('Widget area displayed below the content', 'waboot' ),
			'render_zone' => 'content',
			'render_priority' => 90
		],
		'sidebar_right' => [
			'name' => __('Sidebar right', 'waboot'),
			'description' => __('Widget area displayed in right aside', 'waboot' ),
			'render_zone' => 'aside-secondary'
		],
		'main_bottom' => [
			'name' => __('Main Bottom', 'waboot'),
			'description' => __( 'Widget area displayed below the content and the sidebars.', 'waboot' ),
			'render_zone' => 'main-bottom'
		],
		'footer' => [
			'name' => __('Footer', 'waboot'),
			'description' => __( 'The main widget area displayed in the footer.', 'waboot' ),
			'type' => 'multiple',
			'subareas' => 4, //this will register footer-1, footer-2, footer-3 and footer-4 as widget areas
			'render_zone' => 'footer'
		]
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
		if(isset($arch_obj->name)) return $arch_obj->name;
		return __('Archives', 'waboot');
	}
}

/**
 * Gets the body layout
 *
 * @return string
 */
function get_body_layout(){
	$current_page_type = Utilities::get_current_page_type();
	if($current_page_type == Utilities::PAGE_TYPE_BLOG_PAGE || $current_page_type == Utilities::PAGE_TYPE_DEFAULT_HOME || is_archive()) {
		$layout = \Waboot\functions\get_option('blog_layout');
	}else{
		$layout = \Waboot\functions\get_behavior('layout');
	}
	$layout = apply_filters("waboot/layout/body_layout",$layout);
	return $layout;
}

/**
 * Checks if body layout is full width
 *
 * @return bool
 */
function body_layout_is_full_width(){
	$body_layout = get_body_layout();
	return $body_layout == Layout::LAYOUT_FULL_WIDTH;
}

/**
 * Checks if the body layout has two sidebars
 * 
 * @return bool
 */
function body_layout_has_two_sidebars(){
	$body_layout = get_body_layout();
	if(in_array($body_layout,array("two-sidebars","two-sidebars-right","two-sidebars-left"))){
		return true;
	}else{
		return false;
	}
}

/**
 * Return the class relative to the $blog_layout (by default the current blog layout)
 *
 * @param bool $blog_layout
 * @return mixed
 */
function get_posts_wrapper_class(){
	$classes = [
		"blog-classic"
	];
	$classes = apply_filters("waboot/layout/posts_wrapper/class",$classes);
	return implode(" ",$classes);
}

/**
 * Get the specified sidebar size
 * @param $name ("primary" or "secondary")
 *
 * @return bool
 */
function get_sidebar_size($name){
	$page_type = Utilities::get_current_page_type();

	if($name == "primary"){
		$size = $page_type == Utilities::PAGE_TYPE_BLOG_PAGE || $page_type == Utilities::PAGE_TYPE_DEFAULT_HOME ?
			of_get_option("blog_primary_sidebar_size") : get_behavior('primary-sidebar-size');

		return $size;
	}elseif($name == "secondary"){
		$size = $page_type == Utilities::PAGE_TYPE_BLOG_PAGE || $page_type == Utilities::PAGE_TYPE_DEFAULT_HOME ?
			of_get_option("blog_secondary_sidebar_size") : get_behavior('secondary-sidebar-size');

		return $size;
	}
	return false;
}

/**
 * Returns the sizes of each column available into current layout
 * @return array of integers
 */
function get_cols_sizes(){
	$result = array("main"=>12);
	if (\Waboot\functions\body_layout_has_two_sidebars()) {
		//Primary size
		$primary_sidebar_width = get_sidebar_size("primary");
		if(!$primary_sidebar_width) $primary_sidebar_width = 0;
		//Secondary size
		$secondary_sidebar_width = get_sidebar_size("secondary");
		if(!$secondary_sidebar_width) $secondary_sidebar_width = 0;
		//Main size
		$mainwrap_size = 12 - Layout::layout_width_to_int($primary_sidebar_width) - Layout::layout_width_to_int($secondary_sidebar_width);

		$result = [
			"main" => $mainwrap_size,
			"primary" => Layout::layout_width_to_int($primary_sidebar_width),
			"secondary" => Layout::layout_width_to_int($secondary_sidebar_width)
		];
	}else{
		if(\Waboot\functions\get_body_layout() != Layout::LAYOUT_FULL_WIDTH){
			$primary_sidebar_width = get_sidebar_size("primary");
			if(!$primary_sidebar_width) $primary_sidebar_width = 0;
			$mainwrap_size = 12 - Layout::layout_width_to_int($primary_sidebar_width);

			$result = [
				"main" => $mainwrap_size,
				"primary" => Layout::layout_width_to_int($primary_sidebar_width)
			];
		}
	}
	$result = apply_filters("waboot/layout/get_cols_sizes",$result);
	return $result;
}

/**
 * Filterable version of get_template_part
 *
 * @param $slug
 * @param null $name
 */
function get_template_part($slug,$name = null){
	$page_type = Utilities::get_current_page_type();
	$tpl_part = apply_filters("waboot/layout/template_parts",[$slug,$name],$page_type);
	\get_template_part($tpl_part[0],$tpl_part[1]);
}

/**
 * Print out the custom css file from the theme options value. Called during "update_option".
 *
 * It's a callback set in options "save action" param in options.php
 *
 * @param $option
 * @param $old_value
 * @param $value
 *
 * @return FALSE|string
 */
function deploy_theme_options_css($option, $old_value, $value){
	$input_file_path = apply_filters("waboot/assets/theme_options_style_file/source", get_template_directory()."/assets/src/css/_theme-options.src");
	$output_file_path = apply_filters("waboot/assets/theme_options_style_file/destination", WBF()->resources->get_working_directory()."/theme-options.css");

	if(!is_array($value)) return false;

	$output_string = "";

	$tmpFile = new \SplFileInfo($input_file_path);
	if((!$tmpFile->isFile() || !$tmpFile->isWritable())){
		return false;
	}

	$parsedFile = $output_file_path ? new \SplFileInfo($output_file_path) : null;
	if(!is_dir($parsedFile->getPath())){
		mkdir($parsedFile->getPath());
	}

	$genericOptionfindRegExp = "/{{ ?([a-zA-Z0-9\-_]+) ?}}/";

	$tmpFileObj = $tmpFile->openFile( "r" );
	$parsedFileObj = $parsedFile->openFile( "w" );
	$byte_written = 0;

	while(!$tmpFileObj->eof()){
		$line = $tmpFileObj->fgets();
		//Replace a generic of option
		if(preg_match($genericOptionfindRegExp, $line, $matches)){
			if(array_key_exists( $matches[1], $value)){
				if($value[ $matches[1] ] != ""){
					$line = preg_replace( $genericOptionfindRegExp, $value[$matches[1]], $line);
				}else{
					$line = "\t/*{$matches[1]} is empty*/\n";
				}
			}else{
				$line = "\t/*{$matches[1]} not found*/\n";
			}
		}
		$byte_written += $parsedFileObj->fwrite($line);
	}

	return $output_string;
}