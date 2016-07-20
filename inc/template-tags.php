<?php

namespace Waboot\template_tags;
use WBF\components\mvc\HTMLView;
use WBF\components\utils\Utilities;

/**
 * Displays site title
 * @since 0.13.4
 */
function site_title() {
	$element = apply_filters("waboot/site_title/tag",'h1');
	$display_name = call_user_func(function(){
		$custom_name = of_get_option("custom_site_title","");
		if($custom_name && !empty($custom_name)){
			return $custom_name;
		}else{
			return get_bloginfo("name");
		}
	});
	$link = sprintf( '<a href="%s" title="%s" class="navbar-brand" rel="home">%s</a>', trailingslashit( home_url() ), esc_attr( get_bloginfo( 'name' ) ), $display_name );
	$output = '<' . $element . ' id="site-title" class="site-title">' . $link . '</' . $element .'>';
	echo apply_filters( 'waboot/site_title/markup', $output );
}

/**
 * Displays site description
 * @since 0.13.4
 */
function site_description() {
	if(!of_get_option("show_site_description",0)) return;
	// Use H2
	$element = 'h2';
	// Put it all together
	$description = '<' . $element . ' id="site-description" class="site-description">' . esc_html( get_bloginfo( 'description' ) ) . '</' . $element . '>';
	// Echo the description
	echo apply_filters( 'waboot/site_description/markup', $description );
}

/**
 * Prints the mobile logo
 *
 * @param string $context
 * @param bool $linked
 */
function mobile_logo($context = "header", $linked = false){
	if($linked){
		$tpl = "<a href='%s'><img src='%s' class='img-responsive' /></a>";
		printf($tpl,home_url( '/' ),get_mobile_logo($context));
	}else{
		$tpl = "<img src='%s' class='img-responsive' />";
		printf($tpl,get_mobile_logo($context));
	}
}

/**
 * Get the mobile logo, or an empty string.
 *
 * @param string $context
 *
 * @return string
 */
function get_mobile_logo($context = "header"){
	switch($context){
		case "offcanvas":
			$mobile_logo = \Waboot\functions\get_option('mobile_offcanvas_logo', ""); //todo: add this
			break;
		default:
			$mobile_logo = \Waboot\functions\get_option('mobile_logo', ""); //todo: add this
			break;
	}
	return $mobile_logo;
}

/**
 * Prints the desktop logo
 *
 * @param bool $linked
 */
function desktop_logo($linked = false){
	if($linked){
		$tpl = "<a href='%s'><img src='%s' class='waboot-desktop-logo' /></a>";
		printf($tpl,home_url( '/' ),get_desktop_logo());
	}else{
		$tpl = "<img src='%s' class='waboot-desktop-logo' />";
		printf($tpl,get_desktop_logo());
	}
}

/**
 * Get the desktop logo, or an empty string
 * @return string
 */
function get_desktop_logo(){
	$desktop_logo = \Waboot\functions\get_option('desktop_logo', ""); //todo: add this
	return $desktop_logo;
}

/**
 * Display the content navigation
 *
 * @throws \Exception
 *
 * @param string $nav_id
 * @param bool $show_pagination
 * @param bool $query
 * @param bool $current_page
 */
function post_navigation($nav_id, $show_pagination = false, $query = false, $current_page = false){
	//Setting up the query
	if(!$query){
		global $wp_query;
		$query = $wp_query;
	}else{
		if(!$query instanceof \WP_Query){
			throw new \Exception("Invalid query provided for post_navigation $nav_id");
		}
	}

	//Setup nav class
	$nav_class = 'site-navigation paging-navigation';
	if(is_single()){
		$nav_class .= ' post-navigation';
	}else{
		$nav_class .= ' paging-navigation';
	}
	$nav_class = apply_filters("waboot/layout/post_navigation/nav_class",$nav_class);

	if(!is_single()){
		$can_display_pagination = $query->max_num_pages > 1 && (is_home() || is_archive() || is_search() || is_singular());
		$can_display_pagination = apply_filters("waboot/layout/post_navigation/can_display_navigation",$can_display_pagination,$query,$current_page);
	}else{
		$can_display_pagination = false;
	}

	if($can_display_pagination && $show_pagination){
		$big = 999999999; // need an unlikely integer
		$paginate = paginate_links([
			'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format' => '?paged=%#%',
			'current' => $current_page ? $current_page : max( 1, get_query_var('paged') ),
			'total' => $query->max_num_pages
		]);
		$paginate_array = explode("\n",$paginate);
		foreach($paginate_array as $k => $link){
			$paginate_array[$k] = "<li>".$link."</li>";
		}
		$pagination = implode("\n",$paginate_array);
	}else{
		$pagination = "";
	}

	(new HTMLView("templates/view-parts/post-navigation.php"))->clean()->display([
		'nav_id' => $nav_id,
		'nav_class' => $nav_class,
		'can_display_pagination' => $can_display_pagination,
		'show_pagination' => $show_pagination,
		'pagination' => $pagination,
		'max_num_pages' => $query->max_num_pages
	]);
}

/**
 * Return the $title wrapped between $prefix and $suffix.
 *
 * @param $prefix
 * @param $suffix
 * @param $title
 * @param \WP_Post|null $post
 */
function wrapped_title($prefix,$suffix,$title,\WP_Post $post = null){
	global $wp_query;
	if(!$post) global $post;
	$prefix = apply_filters("waboot/entry/title/prefix",$prefix,$post, $wp_query);
	$suffix = apply_filters("waboot/entry/title/suffix",$suffix,$post, $wp_query);
	echo $prefix.$title.$suffix;
}

/**
 * Prints out the container-relative classes
 */
function container_classes(){
	$classes = "site-main ";
	$classes .= \Waboot\functions\get_option("content_width","container"); //todo: add this
	$classes = apply_filters("waboot/layout/container/classes",$classes);
	echo $classes;
}

/**
 * Prints out the main-relative classes
 */
function main_classes(){
	if(has_filter("waboot_mainwrap_container_class")){
		echo apply_filters('waboot_mainwrap_container_class','content-area col-sm-8'); //backward compatibility
	}else{
		echo apply_filters('waboot/layout/main/classes','content-area col-sm-8');
	}
}

/**
 * Prints out posts wrapper classes
 */
function posts_wrapper_class(){
	echo \Waboot\functions\get_posts_wrapper_class();
}