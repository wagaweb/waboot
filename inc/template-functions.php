<?php

if(!function_exists("waboot_get_blog_layout")):
	/**
	 * Return the current blog layout or the default one ("classic")
	 * @return bool|string
	 */
	function waboot_get_blog_layout(){
		$blog_style = of_get_option("waboot_blogpage_layout");
		if (!$blog_style || $blog_style == "") $blog_style = "classic";

		return $blog_style;
	}
endif;

if(!function_exists("waboot_get_blog_class")):
	function waboot_get_blog_class($blog_layout = "classic"){
		$classes = array(
			"blog-".$blog_layout
		);

		if($blog_layout == "masonry"){
			$classes[] = "row";
		}

		return implode(" ",$classes);
	}
endif;

if(!function_exists("waboot_get_index_page_title")):
	function waboot_get_index_page_title(){
		return single_post_title('', false);
	}
endif;

if(!function_exists("waboot_get_wc_shop_page_title")):
	function waboot_get_wc_shop_page_title(){
		if(!function_exists("woocommerce_get_page_id")) return false;
		$shop_page_id = wc_get_page_id('shop');
		if($shop_page_id){
			$page_title = get_the_title( $shop_page_id );
			return $page_title;
		}else{
			return false;
		}
	}
endif;

if(!function_exists("waboot_get_archive_page_title")):
	function waboot_get_archive_page_title(){
		global $post;
		if ( is_category() ) {
			$title = single_cat_title('',false);
		} elseif ( is_tag() ) {
			$title = single_tag_title('',false);
		} elseif ( is_author() ) {
			$author_name = get_the_author_meta("display_name",$post->post_author);
			$title = sprintf( __( 'Author: %s', 'waboot' ), '<span class="vcard"><a class="url fn n" href="' . get_author_posts_url( $post->post_author ) . '" title="' . esc_attr( $author_name ) . '" rel="me">' . $author_name . '</a></span>' );
		} elseif ( is_day() ) {
			$title = sprintf( __( 'Day: %s', 'waboot' ), '<span>' . get_the_date('', $post->ID) . '</span>' );
		} elseif ( is_month() ) {
			$title = sprintf( __( 'Month: %s', 'waboot' ), '<span>' . get_the_date('F Y', $post->ID ) . '</span>' );
		} elseif ( is_year() ) {
			$title = printf( __( 'Year: %s', 'waboot' ), '<span>' . get_the_date('Y', $post->ID ) . '</span>' );
		} elseif ( is_tax( 'post_format', 'post-format-aside' ) ) {
			$title = __( 'Asides', 'waboot' );
		} elseif ( is_tax( 'post_format', 'post-format-gallery' ) ) {
			$title = __( 'Galleries', 'waboot');
		} elseif ( is_tax( 'post_format', 'post-format-image' ) ) {
			$title = __( 'Images', 'waboot');
		} elseif ( is_tax( 'post_format', 'post-format-video' ) ) {
			$title = __( 'Videos', 'waboot' );
		} elseif ( is_tax( 'post_format', 'post-format-quote' ) ) {
			$title = __( 'Quotes', 'waboot' );
		} elseif ( is_tax( 'post_format', 'post-format-link' ) ) {
			$title = __( 'Links', 'waboot' );
		} elseif ( is_tax( 'post_format', 'post-format-status' ) ) {
			$title = __( 'Statuses', 'waboot' );
		} elseif ( is_tax( 'post_format', 'post-format-audio' ) ) {
			$title = __( 'Audios', 'waboot' );
		} elseif ( is_tax( 'post_format', 'post-format-chat' ) ) {
			$title = __( 'Chats', 'waboot' );
		} else {
			$arch_obj = get_queried_object();
			if(isset($arch_obj->name)){
				$title = $arch_obj->name;
			}else{
				$title = __( 'Archives', 'waboot' );
			}
		}

		return apply_filters( 'get_the_archive_title', $title );
	}
endif;

if(!function_exists("waboot_get_compiled_stylesheet_uri")):
	function waboot_get_compiled_stylesheet_uri(){
		$base_uri = get_stylesheet_directory_uri()."/assets/dist/css";
		if(is_multisite()){
			$uri = $base_uri."/mu";
		}else{
			$uri = $base_uri;
		}
		return apply_filters("wbft/compiler/output/uri",$uri);
	}
endif;

if(!function_exists("waboot_get_compiled_stylesheet_directory")):
	function waboot_get_compiled_stylesheet_directory(){
		$base_dir = get_stylesheet_directory()."/assets/dist/css";
		if(is_multisite()){
			if(!is_dir($base_dir."/mu")){
				mkdir($base_dir."/mu");
			}
			$dir = $base_dir."/mu";
		}else{
			$dir = $base_dir;
		}
		return apply_filters("wbft/compiler/output/directory",$dir);
	}
endif;

if(!function_exists("waboot_get_compiled_stylesheet_name")):
	function waboot_get_compiled_stylesheet_name(){
		$theme = wp_get_theme();
		if(is_child_theme()){
			$filename = $theme->stylesheet;
		}else{
			$filename = $theme->template;
		}
		return apply_filters("wbft/compiler/output/filename",$filename);
	}
endif;

if(!function_exists('waboot_get_available_socials')):
	function waboot_get_available_socials(){
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
endif;

if(!function_exists("waboot_get_body_layout")):
	function waboot_get_body_layout(){
		if(wbft_current_page_type() == "blog_page" || wbft_current_page_type() == "default_home" || is_archive()) {
			$layout = of_get_option('waboot_blogpage_sidebar_layout');
		}else{
			$layout = get_behavior('layout');
		}
		$layout = apply_filters("waboot/layout/body_layout/get",$layout);
		return $layout;
	}
endif;

if(!function_exists("waboot_body_layout_has_two_sidebars")):
	function waboot_body_layout_has_two_sidebars(){
		$body_layout = waboot_get_body_layout();
		if(in_array($body_layout,array("two-sidebars","two-sidebars-right","two-sidebars-left"))){
			return true;
		}else{
			return false;
		}
	}
endif;

if(!function_exists("waboot_get_available_body_layouts")){
	function waboot_get_available_body_layouts(){

		$imagepath = get_template_directory_uri() . '/assets/images/theme_options/';

		return apply_filters("waboot_body_layouts",array(
			array(
				"name" => __("No sidebar","waboot"),
				"value" => "full-width",
				"thumb"   => $imagepath . "behaviour/no-sidebar.png"
			),
			array(
				"name" => __("Sidebar right","waboot"),
				"value" => "sidebar-right",
				"thumb"   => $imagepath . "behaviour/sidebar-right.png"
			),
			array(
				"name" => __("Sidebar left","waboot"),
				"value" => "sidebar-left",
				"thumb"   => $imagepath . "behaviour/sidebar-left.png"
			),
			array(
				"name" => __("2 Sidebars","waboot"),
				"value" => "two-sidebars",
				"thumb"   => $imagepath . "behaviour/sidebar-left-right.png"
			),
			array(
				"name" => __("2 Sidebars right","waboot"),
				"value" => "two-sidebars-right",
				"thumb"   => $imagepath . "behaviour/sidebar-right-2.png"
			),
			array(
				"name" => __("2 Sidebars left","waboot"),
				"value" => "two-sidebars-left",
				"thumb"   => $imagepath . "behaviour/sidebar-left-2.png"
			),
			'_default' => 'sidebar-right'
		));
	}
}

if(!function_exists('waboot_has_sidebar')):
	function waboot_has_sidebar($prefix){
		$has_sidebar = false;
		for ($i = 1; $i <= 4; $i++) {
			if (is_active_sidebar($prefix . "-" . $i)) {
				$has_sidebar = true;
			}
		}
		return $has_sidebar;
	}
endif;

if(!function_exists("waboot_breadcrumb_trail")):
	/**
	 * Backward compatibility for wbf_breadcrumb_trail
	 * @param array $args
	 */
	function waboot_breadcrumb_trail( $args = array() ){
		wbft_breadcrumb_trail($args);
	}
endif;

// ###############################
// ###############################
// LEGACY CODE
// ###############################
// ###############################

if(!function_exists("waboot_get_uri_path_after")):
	/**
	 * Get the uri parts after specified tag. Eg: if the uri is "/foo/bar/zor/", calling waboot_get_uri_path_after(foo) will return: array("bar","zor")
	 * @param $tag
	 * @return array
	 */
	function waboot_get_uri_path_after($tag){
		return wbft_get_uri_path_after($tag);
	}
endif;

if(!function_exists('waboot_get_the_category')):
	/**
	 * Get the post categories ordered by ID. If the post is a custom post type it retrieve the specified $taxonomy terms or the first registered taxonomy
	 * @param null $post_id
	 * @param null $taxonomy the taxonomy to retrieve if the POST is a custom post type
	 * @param bool $ids_only retrieve only the ID of the categories
	 * @internal param null $the_post
	 * @return array
	 */
	function waboot_get_the_category($post_id = null, $taxonomy = null, $ids_only = false){
		return wbft_get_the_category($post_id,$taxonomy,$ids_only);
	}
endif;

if(!function_exists('waboot_get_top_categories')):
	/**
	 * Get the top level categories
	 * @param null $taxonomy
	 * @return array
	 */
	function waboot_get_top_categories($taxonomy = null){
		return wbft_get_top_categories($taxonomy);
	}
endif;

if(!function_exists('waboot_get_top_category')):
	/**
	 * Gets top level category of the current or specified post
	 * @param string $return_value "id" or "slug". If empty the category object is returned.
	 * @return string|object
	 */
	function waboot_get_top_category($return_value = "", $post_id = null) {
		return wbft_get_top_category($return_value,$post_id);
	}
endif;

if(!function_exists('waboot_get_first_taxonomy')):
	/**
	 * Get the first registered taxonomy of a custom post type
	 * @param null $post_id
	 * @return string
	 */
	function waboot_get_first_taxonomy($post_id = null){
		return wbft_get_first_taxonomy($post_id);
	}
endif;

if(!function_exists('waboot_sort_categories_by_id')):
	/**
	 * Sort the categories of a post by ID (ASC)
	 * @param $a
	 * @param $b
	 * @return int
	 */
	function waboot_sort_categories_by_id($a,$b){
		return wbft_sort_categories_by_id($a,$b);
	}
endif;