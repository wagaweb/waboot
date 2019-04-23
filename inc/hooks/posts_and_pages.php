<?php

namespace Waboot\hooks;
use function Waboot\functions\blog_page_can_display_title;
use function Waboot\functions\get_archive_option;
use WBF\components\mvc\HTMLView;
use WBF\components\utils\Query;
use WBF\components\utils\Utilities;

//Header:
add_action("waboot/entry/header",__NAMESPACE__."\\display_title");
add_action("waboot/main-content/before",__NAMESPACE__."\\display_singular_title");

/*add_action("waboot/layout/archive/page_title/before",__NAMESPACE__."\\display_title_wrapper_start",10);
add_action("waboot/layout/archive/page_title/after",__NAMESPACE__."\\display_title_wrapper_end",90);

add_action("waboot/layout/singular/page_title/before",__NAMESPACE__."\\display_title_wrapper_start",10);
add_action("waboot/layout/singular/page_title/after",__NAMESPACE__."\\display_title_wrapper_end",90);*/

add_action("waboot/layout/archive/page_title/after",__NAMESPACE__."\\display_taxonomy_description",20);

//Footer:
add_action("waboot/entry/footer",__NAMESPACE__."\\entry_footer_wrapper_start",10);
add_action("waboot/entry/footer",__NAMESPACE__."\\display_post_date",20);
add_action("waboot/entry/footer",__NAMESPACE__."\\display_post_author",30);
add_action("waboot/entry/footer",__NAMESPACE__."\\display_post_categories",40);
add_action("waboot/entry/footer",__NAMESPACE__."\\display_post_tags",50);
add_action("waboot/entry/footer",__NAMESPACE__."\\display_post_comment_link",60);
add_action("waboot/entry/footer",__NAMESPACE__."\\entry_footer_wrapper_end",9999);

/**
 * Display entry title in entry header or outsite the entry itself. This is called both for a single entry and a list of entries (archives, blog page, index...)
 *
 * @param string $type if we are cycling through a single or a list of entries.
 */
function display_title($type = 'single'){

	if($type === ""){
		$type = "single";
	}

	switch ($type){
		case 'single':
			display_singular_title();
			break;
		case 'list':
			display_list_element_title();
			break;
	}
}

/**
 * Display the title of a node in a list context (a loop over a list of posts)
 */
function display_list_element_title(){
	global $post;

	$title = get_the_title($post->ID);

	$title = apply_filters("waboot/post_list/entry/title",$title,$post);
	$can_display_title = apply_filters("waboot/post_list/entry/title/display_flag",true,$post);

	if(!$can_display_title) return;

	$tpl = apply_filters("waboot/post_list/entry/title/tpl","templates/view-parts/entry-title-list.php",$post);
	$tpl_args = [
		'title' => $title
	];
	$tpl_args = apply_filters("waboot/post_list/entry/title/tpl_args",$tpl_args,$post);

	(new HTMLView($tpl))->display($tpl_args);
}

/**
 * Display the title of a single node in a singular context (not in a loop over a list of posts)
 *
 * In Waboot we can either display that title in "waboot/site-main/before" (in wrapper-start.php) or in the content-specific templates (eg: page.php). The latter case occurs if
 * the option\behavior of 'title position' in 'bottom'
 */
function display_singular_title(){
	global $post;
	$page_type = Utilities::get_current_page_type();

	$current_title_context = current_filter() === "waboot/entry/header" ? "bottom" : "top"; //current_filter() is expected to be "waboot/main-content/before" || "waboot/entry/header"

	if($page_type === Utilities::PAGE_TYPE_DEFAULT_HOME || $page_type === Utilities::PAGE_TYPE_BLOG_PAGE){
		$title = \Waboot\functions\get_index_page_title();
		if(!isset($title)){
			//We are in the "default_home" case
			$title = apply_filters('waboot/default_home/title','');
		}
		//$can_display_title = (bool) \Waboot\functions\get_option('blog_display_title') && \Waboot\functions\get_option('blog_title_position') === $current_title_context;
		$can_display_title = blog_page_can_display_title($current_title_context);
	}elseif(is_search()){
		$title = sprintf( __( 'Search Results for: %s', 'waboot' ), '<span>' . get_search_query() . '</span>' );
		$can_display_title = (bool) \Waboot\functions\get_option('blog_display_title') && \Waboot\functions\get_option('blog_title_position') === $current_title_context;
	}elseif(is_archive()){
		$title = \Waboot\functions\get_archive_page_title();
		if(is_category()){
			$can_display_title = (bool) \Waboot\functions\get_option('blog_display_title') && \Waboot\functions\get_option('blog_title_position') === $current_title_context;
		}else{
			$can_display_title = (bool) get_archive_option('display_title') && get_archive_option('title_position') === $current_title_context;
		}
	}elseif(is_singular()){
		$title = get_the_title($post->ID);
		$can_display_title =  (bool) \Waboot\functions\get_behavior('show-title') && \Waboot\functions\get_behavior('title-position') === $current_title_context;
	}

	if(!isset($title)){
		$title = $post instanceof \WP_Post ? get_the_title($post->ID) : "";
	}

	if(!isset($can_display_title)){
		$can_display_title = true;
	}

	$title = apply_filters("waboot/singular/title",$title,$current_title_context);
	$can_display_title = apply_filters("waboot/singular/title/display_flag",$can_display_title,$current_title_context);

	if(!$can_display_title) return;

	$tpl_args = [
		'title' => $title,
	];

	//todo: can we replace is_home() here with $page_type === Utilities::PAGE_TYPE_BLOG_PAGE ?
    if(is_archive() || is_home()){
        $tpl = "templates/view-parts/archive-title.php";
        if(is_archive()){
            $tpl_args['title_position'] = get_archive_option('title_position');
        }else{
            $tpl_args['title_position'] = \Waboot\functions\get_option('blog_title_position');
        }
    }else{
		$tpl = "templates/view-parts/entry-title-singular.php";
		$tpl_args['title_position'] = \Waboot\functions\get_behavior('title-position');
	}

	$tpl = apply_filters("waboot/singular/title/tpl",$tpl,$current_title_context);
	$tpl_args = apply_filters("waboot/singular/title/tpl_args",$tpl_args);

	(new HTMLView($tpl))->display($tpl_args);
}

/**
 * Prints HTML with date posted information for the current post.
 * 
 * @param null $print_relative
 */
function display_post_date($print_relative = null){
	// Return early if theme options are set to hide date
	if(!\Waboot\functions\get_option('show_post_date', true)) return;

	$tpl = "templates/view-parts/entry-date.php";

    if(!isset($print_relative)){
        $relative_time = \Waboot\functions\get_option('show_post_relative_time', false);
    }else{
        $relative_time = $print_relative;
    }

	(new HTMLView($tpl))->display([
		'tag_date' => esc_attr(get_the_date('c')),
		'date' => !$relative_time ? esc_html( get_the_date() ) : sprintf( _x( '%s ago', 'Relative date output for entry footer' ,'waboot' ), human_time_diff( get_the_date( 'U' ), current_time( 'timestamp' ) ) )
	]);
}

/**
 * Prints HTML with meta information for the current post's author.
 */
function display_post_author(){
	// Return early if theme options are set to hide author
	if(!\Waboot\functions\get_option('show_post_author', true)) return;

	$tpl = "templates/view-parts/entry-author.php";

	(new HTMLView($tpl))->display([
		'author_url' =>  esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		'author_title' => esc_attr(sprintf(__( 'View all posts by %s', 'waboot' ), get_the_author())),
		'author_name' => esc_html(get_the_author())
	]);
}

/**
 * Display the list of categories on a post in hierarchical order
 *
 * @use \Waboot\template_tags\get_the_terms_list_hierarchical()
 */
function display_post_categories(){
	// Return early if theme options are set to hide categories
	if (!\Waboot\functions\get_option('show_post_categories', true)) return;

	echo \Waboot\template_tags\get_the_terms_list_hierarchical(get_the_ID(), 'category', '<span class="cat-links">', ', ', '</span>');
}

/**
 * Display the list of post tags
 */
function display_post_tags(){
	// Return early if theme options are set to hide tags
	if(!\Waboot\functions\get_option('show_post_tags', true)) return;

	$post_tags = get_the_tags();

	if(is_array($post_tags) && !empty($post_tags)){
		$wrapper_start = '<span class="tags-links">';
		$wrapper_end = '</span>';
		$num_tags = count( $post_tags );

		echo $wrapper_start;

		$tag_count = 1;
		foreach( $post_tags as $tag ) {
			$html_before = '<a href="%s" rel="tag nofollow" class="tag-text">';
			$html_after = '</a>';
			$sep = $tag_count < $num_tags ? ", " : '';

			echo sprintf($html_before,get_tag_link($tag->term_id)) . $tag->name . $html_after . $sep;

			$tag_count++;
		}

		echo $wrapper_end;
	}
}

/**
 * Display the "leave comment" link.
 */
function display_post_comment_link(){
	// Return early if theme options are set to hide comment link
	if(!\Waboot\functions\get_option('show_post_comments_link',true)) return;

	$tpl = "templates/view-parts/entry-comment-link.php";

	(new HTMLView($tpl))->display();
}

/**
 * Prints out the entry footer wrapper start
 */
function entry_footer_wrapper_start(){
	echo '<footer class="entry__footer">';
}

/**
 * Prints out the entry footer wrapper end
 */
function entry_footer_wrapper_end(){
	echo '</footer>';
}

/**
 * Prints out the taxonomy descriptions
 */
function display_taxonomy_description(){
	$tpl = "templates/view-parts/archive-description.php";

	(new HTMLView($tpl))->display();
}

/**
 * Prints out the title wrapper start
 */
function display_title_wrapper_start(){
	$can_display = false;

	if( (is_home() || is_search()) && \Waboot\functions\get_option('blog_title_position') === 'top'){
		$can_display = true;
	}elseif(is_archive()) {
		if(get_archive_option('title_position') === 'top'){
			$can_display = true;
		}
	}else{
		if(\Waboot\functions\get_behavior('title-position') === 'top'){
			$can_display = true;
		}
	}

	if($can_display){
		echo '<div class="entry__wrapper">';
	}
}

/**
 * Prints out the title wrapper end
 */
function display_title_wrapper_end(){
	$can_display = false;

	if( (is_home() || is_search()) && \Waboot\functions\get_option('blog_title_position') === 'top'){
		$can_display = true;
	}elseif(is_archive()) {
		if(get_archive_option('title_position') === 'top'){
			$can_display = true;
		}
	}else{
		if(\Waboot\functions\get_behavior('title-position') === 'top'){
			$can_display = true;
		}
	}

	if($can_display){
		echo '</div>';
	}
}