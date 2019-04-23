<?php

namespace Waboot\template_tags;
use WBF\components\mvc\HTMLView;
use WBF\components\utils\Utilities;

/**
 * Renders a zone
 *
 * @param string $slug
 */
function render_zone($slug){
	if(!function_exists('WabootLayout')) return;
	try{
		$wbl = WabootLayout();
		if($wbl){
			$wbl->render_zone($slug);
		}
	}catch(\Exception $e){
		echo $e->getMessage();
	}
}

/**
 * Executes <head> actions
 */
function site_head(){
	do_action("waboot/head/start");
	wp_head();
	do_action("waboot/head/end");
}

/**
 * Displays site title
 */
function site_title() {
	$display_name = get_site_title();
    $output = $display_name;
	echo apply_filters( 'waboot/site_title/markup', $output );
}

/**
 * Get the site title
 *
 * @return string
 */
function get_site_title(){
	$custom_name = \Waboot\functions\get_option("custom_site_title","");
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
	if(!\Waboot\functions\get_option("show_site_description",0)) return;
	// Use H2
	$element = 'h2';
	// Put it all together
	$description = '<' . $element . ' id="site-description" class="site-description">' . esc_html( get_bloginfo( 'description' ) ) . '</' . $element . '>';
	// Echo the description
	echo apply_filters( 'waboot/site_description/markup', $description );
}

/**
 * Prints the desktop logo
 *
 * @param bool $linked
 * @param string $class
 */
function desktop_logo($linked = false, $class = ''){
    if($linked){
        $tpl = '<a href="%s"><img src="%s" class="'.$class.'" /></a>';
        printf($tpl,home_url( '/' ),get_desktop_logo());
    }else{
        $tpl = '<img src="%s" class="'.$class.'" />';
        printf($tpl,get_desktop_logo());
    }
}

/**
 * Get the desktop logo, or an empty string
 * @return string
 */
function get_desktop_logo(){
    $desktop_logo = \Waboot\functions\get_option('desktop_logo', ""); //
    return $desktop_logo;
}

/**
 * Prints the mobile logo
 *
 * @param bool $linked
 * @param string $class
 */
function mobile_logo($linked = false, $class = '') {
    if($linked){
        $tpl = '<a href="%s"><img src="%s" class="'.$class.'" /></a>';
        printf($tpl,home_url( '/' ),get_mobile_logo());
    }else{
        $tpl = '<img src="%s" class="'.$class.'" />';
        printf($tpl,get_mobile_logo());
    }
}

/**
 * Get the mobile logo, or an empty string
 * @return string
 */
function get_mobile_logo(){
    $mobile_logo = \Waboot\functions\get_option('mobile_logo', "");
    return $mobile_logo;
}

/**
 * Print out the blog page title
 */
function blog_page_title(){
    $title = \Waboot\functions\get_index_page_title();
    $tpl = "templates/view-parts/archive-title.php";
    (new HTMLView($tpl))->display([
        'title' => $title,
        'title_position' => \Waboot\functions\get_option('blog_title_position')
    ]);
}

/**
 * Print out the index page title
 */
function index_page_title(){
	$title = \Waboot\functions\get_index_page_title();
	$tpl = "templates/view-parts/entry-title-singular.php";
	(new HTMLView($tpl))->display([
		'title' => $title,
		'title_position' => \Waboot\functions\get_behavior('title-position')
	]);
}

/**
 * Print out the archive page title
 */
function archive_page_title(){
    $title = \Waboot\functions\get_archive_page_title();
    $tpl = "templates/view-parts/archive-title.php";
    (new HTMLView($tpl))->display([
        'title' => $title
    ]);
}

/**
 * Display the content navigation
 *
 * @throws \Exception
 *
 * @param string $nav_id (you can use 'nav-below' or 'nav-above')
 * @param bool $show_pagination
 * @param bool $query
 * @param bool $current_page
 * @param string $paged_var_name You can supply different paged var name for multiple pagination. The name must be previously registered with add_rewrite_tag()
 */
function post_navigation($nav_id, $show_pagination = true, $query = false, $current_page = false, $paged_var_name = "paged"){
	$can_show_nav = \call_user_func(function() use($nav_id){
		if(is_category() || is_home()){
			switch($nav_id){
				case 'nav-below':
					return (bool) \Waboot\functions\get_option('show_content_nav_below');
					break;
				case 'nav-above':
					return (bool) \Waboot\functions\get_option('show_content_nav_above');
					break;
				default:
					return false;
					break;
			}
		}elseif(is_single()){
			switch($nav_id){
				case 'nav-below':
					return (bool) \Waboot\functions\get_behavior('show-content-nav-below');
					break;
				case 'nav-above':
					return (bool) \Waboot\functions\get_behavior('show-content-nav-above');
					break;
				default:
					return false;
					break;
			}
		}
		return true;
	});
	if(!$can_show_nav) return; // Return early if theme options are set to hide nav

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
	$nav_class = 'site-navigation';
	if(is_single()){
		$nav_class .= ' post-navigation';
	}else{
		$nav_class .= ' paging-navigation';
	}
	$nav_class = apply_filters("waboot/layout/post_navigation/nav_class",$nav_class);

	if(is_single()){
		$can_display_pagination = false; //Single post cannot have pagination ([1],[2]... [n] links)
	}else{
		$can_display_pagination = $query->max_num_pages > 1 && (is_home() || is_archive() || is_search() || is_singular());
		$can_display_pagination = apply_filters("waboot/layout/post_navigation/can_display_navigation",$can_display_pagination,$query,$current_page);
	}

	$show_pagination = apply_filters('waboot/layout/post_navigation/display_numeric_pagination',$show_pagination,$nav_id);

	if($can_display_pagination && $show_pagination){
		$big = 999999999; // need an unlikely integer
		if($paged_var_name !== "paged"){
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
	$classes[] = WabootLayout()->get_container_grid_class(\Waboot\functions\get_behavior("content-width"));
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
		echo apply_filters('waboot_mainwrap_container_class','wbcol--8'); //backward compatibility
	}else{
		echo apply_filters('waboot/layout/main/classes','wbcol--8');
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
 * @param int|null $length
 * @param string|null $more
 * @param int|null $post_id
 * @param bool $fallback_to_content use the post content if the excerpt is empty
 *
 * @return string
 */
function get_the_trimmed_excerpt($length = null,$more = null,$post_id = null, $fallback_to_content = false){
	if(!isset($length)){
		$excerpt_length = apply_filters( 'excerpt_length', 55 );
	}else{
		$excerpt_length = $length;
	}
	if(!isset($more)){
		$excerpt_more = apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );
	}else{
		$excerpt_more = $more;
	}

	if(is_string($fallback_to_content)){ //backward compatibility
		$fallback_to_content = false;
		if($fallback_to_content == "content_also"){
			$fallback_to_content = true;
		}elseif($fallback_to_content == "excerpt_only"){
			$fallback_to_content = false;
		}
	}

	if(isset($post_id)){
		$post = get_post($post_id);
		if($fallback_to_content && $post->post_excerpt == ""){
			$text = apply_filters('the_content', $post->post_content);
		}else{
			$text = $post->post_excerpt;
		}
	}else{
		global $post;
		if($fallback_to_content && $post->post_excerpt == ""){
			$text = get_the_content();
		}else{
			$text = get_the_excerpt();
		}
	}

	return wp_trim_words($text,$excerpt_length,$excerpt_more);
}

/**
 * A version of the_excerpt() that applies the trim function to the predefined excerpt as well
 *
 * @param int|null $length
 * @param string|null $more
 * @param int|null $post_id
 * @param bool $fallback_to_content use the post content if the excerpt is empty
 */
function the_trimmed_excerpt($length = null,$more = null,$post_id = null, $fallback_to_content = false){
    echo get_the_trimmed_excerpt($length,$more,$post_id,$fallback_to_content);
}