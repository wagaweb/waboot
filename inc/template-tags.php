<?php

namespace Waboot\template_tags;
use WBF\components\mvc\HTMLView;
use WBF\components\utils\Utilities;

/**
 * Displays site title
 */
function site_title() {
	$element = apply_filters("waboot/site_title/tag",'h1');
	$display_name = get_site_title();
	$link = sprintf( '<a href="%s" title="%s" class="navbar-brand" rel="home">%s</a>', trailingslashit( home_url() ), esc_attr( get_bloginfo( 'name' ) ), $display_name );
	$output = '<' . $element . ' id="site-title" class="site-title">' . $link . '</' . $element .'>';
	echo apply_filters( 'waboot/site_title/markup', $output );
}

/**
 * Get the site title
 *
 * @return string
 */
function get_site_title(){
	$custom_name = of_get_option("custom_site_title","");
	if($custom_name && !empty($custom_name)){
		return $custom_name;
	}else{
		return get_bloginfo("name");
	}
}

/**
 * Displays site description
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
 * Print out the index page title
 */
function index_page_title(){
	$title = \Waboot\functions\get_index_page_title();
	$tpl = "templates/view-parts/entry-title-singular.php";
	(new HTMLView($tpl))->display([
		'title' => $title
	]);
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
 * @param string $paged_var_name You can supply different paged var name for multiple pagination. The name must be previously registered with add_rewrite_tag()
 */
function post_navigation($nav_id, $show_pagination = false, $query = false, $current_page = false, $paged_var_name = "paged"){
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
		if($paged_var_name != "paged"){
			$base =  add_query_arg([
				$paged_var_name => "%#%"
			]);
			$base = home_url().$base;
		}else{
			$base =  str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) );
		}
		$paginate = paginate_links([
			'base' => $base,
			'format' => '?'.$paged_var_name.'=%#%',
			'current' => $current_page ? intval($current_page) : max( 1, intval(get_query_var($paged_var_name)) ),
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
	$classes[] = "site-main";
	$classes[] = \Waboot\functions\get_behavior("content-width");
	$classes = apply_filters("waboot/layout/container/classes",$classes);
	if(is_array($classes)){
		$classes = implode(" ",$classes);
	}
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

/**
 * Prints the attached image with a link to the next attached image.
 */
function the_attached_image() {
	$post = get_post();
	$attachment_size = apply_filters( 'waboot/post_types/attachment/size', [1200, 1200] );
	$next_attachment_url = wp_get_attachment_url();

	/*
	 * Grab the IDs of all the image attachments in a gallery so we can get the
	 * URL of the next adjacent image in a gallery, or the first image (if
	 * we're looking at the last image in a gallery), or, in a gallery of one,
	 * just the link to that image file.
	 */
	$attachment_ids = get_posts([
		'post_parent' => $post->post_parent,
		'fields' => 'ids',
		'numberposts' => -1,
		'post_status' => 'inherit',
		'post_type' => 'attachment',
		'post_mime_type' => 'image',
		'order' => 'ASC',
		'orderby' => 'menu_order ID'
	]);

	//If there is more than 1 attachment in a gallery...
	if(count($attachment_ids) > 1){
		foreach($attachment_ids as $attachment_id){
			if($attachment_id == $post->ID){
				$next_id = current($attachment_ids);
				break;
			}
		}
		//get the URL of the next image attachment... or get the URL of the first image attachment.
		$next_attachment_url = isset($next_id) ? get_attachment_link($next_id) : get_attachment_link(array_shift($attachment_ids));
	}

	printf( '<a href="%1$s" title="%2$s" rel="attachment">%3$s</a>',
		esc_url( $next_attachment_url ),
		the_title_attribute( array( 'echo' => false ) ),
		wp_get_attachment_image( $post->ID, $attachment_size )
	);
}

/**
 * Returns the first post link and/or post content without the link.
 *
 * Used for the "Link" post format.
 *
 * @param string $output_type "link" or "post_content"
 * @return string Link or Post Content without link.
 */
function get_first_link_or_post_content($output_type = "link"){

	$post_content = get_the_content();

	$link = preg_match( '/<a\s[^>]*?href=[\'"](.+?)[\'"][^>]*>[^>]*>/is', $post_content, $matches );

	if($link){
		$link_url = $matches[1];
		$post_content = substr( $post_content, strlen( $matches[0] ) );
		if(!$post_content) $post_content = "";
	}

	$output = "";

	switch($output_type){
		case "link":
			if(isset($link_url)){
				$output = $link_url;
			}
			break;
		case "post_content":
			if(isset($post_content)){
				$output = $post_content;
			}
			break;
	}

	return $output;
}

/**
 * A version of the_excerpt() that applies the trim function to the predefined excerpt as well
 *
 * @param bool $length
 * @param bool|null $more
 * @param null $post_id
 * @param string $use is "content_also" then the content will be trimmed if the excerpt is empty
 */
function the_trimmed_excerpt($length = false,$more = null,$post_id = null, $use = "excerpt_only"){
    if(is_bool($length) && !$length){
        $excerpt_length = apply_filters( 'excerpt_length', 55 );
    }else{
        $excerpt_length = $length;
    }
    if(is_null($more)){
        $excerpt_more = apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );
    }else{
        $excerpt_more = $more;
    }

    if(isset($post_id)){
        $post = get_post($post_id);
        if($use == "content_also" && $post->post_excerpt == ""){
            $text = apply_filters('the_content', $post->post_content);
        }else{
            $text = $post->post_excerpt;
        }
    }else{
        global $post;
        if($use == "content_also" && $post->post_excerpt == ""){
            $text = get_the_content();
        }else{
            $text = get_the_excerpt();
        }
    }

    echo  wp_trim_words($text,$excerpt_length,$excerpt_more);
}