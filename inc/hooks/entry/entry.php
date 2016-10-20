<?php

namespace Waboot\hooks\entry;
use WBF\components\mvc\HTMLView;
use WBF\components\utils\Utilities;

//Header:
add_action("waboot/entry/header",__NAMESPACE__."\\display_title");
add_action("waboot/site-main/before",__NAMESPACE__."\\display_title");

//Footer:
add_action("waboot/entry/footer",__NAMESPACE__."\\entry_footer_wrapper_start",10);
add_action("waboot/entry/footer",__NAMESPACE__."\\display_post_date",11);
add_action("waboot/entry/footer",__NAMESPACE__."\\display_post_author",12);
add_action("waboot/entry/footer",__NAMESPACE__."\\display_post_categories",13);
add_action("waboot/entry/footer",__NAMESPACE__."\\display_post_tags",14);
add_action("waboot/entry/footer",__NAMESPACE__."\\display_post_comment_link",15);
add_action("waboot/entry/footer",__NAMESPACE__."\\entry_footer_wrapper_end",9999);

/**
 * Display entry title in entry header or outsite the entry itself. This is called both for a single entry and a list of entries (archives, blog page, index...)
 *
 * @param string $type if we are cycling through a single or a list of entries.
 */
function display_title($type = 'single'){
    global $post;

	if($type == ""){
		$type = "single";
	}

	$current_title_position = current_filter() == "waboot/entry/header" ? "bottom" : "top";

	if(Utilities::get_current_page_type() == Utilities::PAGE_TYPE_DEFAULT_HOME){
		//Here we are in the default homepage and we are parsing one of the many posts.
		$title = get_the_title($post->ID);
		$can_display_title = $current_title_position == "bottom"; //So always show the title inside the entry.
	}else{
		switch($current_title_position){
			//Print entry header INSIDE the entries:
			case "bottom":
				$title = get_the_title($post->ID);
				if(is_archive()){
					//Here we are in an archive and we are parsing one of the many posts.
					$can_display_title = true; //Always display title in the archives
				}else{
					if(Utilities::get_current_page_type() != Utilities::PAGE_TYPE_BLOG_PAGE){ //The display of blog\index page title is handled into blog.php
						//Handles the posts titles... (here we are in a single post)
						$can_display_title = (bool) \Waboot\functions\get_behavior('show-title') == true && \Waboot\functions\get_behavior('title-position') == $current_title_position;
					}else{
						//Here we are in user-defined blog page
						$can_display_title = true;
					}
				}
				break;
			//Print entry header OUTSIDE the single entry:
			case "top":
				if(Utilities::get_current_page_type() == Utilities::PAGE_TYPE_BLOG_PAGE){
					$title = \Waboot\functions\get_index_page_title();
					$can_display_title = (bool) \Waboot\functions\get_option('blog_display_title') == true && \Waboot\functions\get_option('blog_title_position') == $current_title_position;
				}elseif(is_archive()){
					$title = \Waboot\functions\get_archive_page_title();
					$can_display_title =  (bool) \Waboot\functions\get_option('blog_display_title') == true && \Waboot\functions\get_option('blog_title_position') == $current_title_position;
				}elseif(is_singular()){
					$title = get_the_title($post->ID);
					$can_display_title =  (bool) \Waboot\functions\get_behavior('show-title') == true && \Waboot\functions\get_behavior('title-position') == $current_title_position;
				}
				break;
		}
	}

	if(!isset($title)){
		$title = $post instanceof \WP_Post ? get_the_title($post->ID) : "";
	}

	if(!isset($can_display_title)){
		$can_display_title = true;
	}

	$title = apply_filters("waboot/entry/title",$title,$current_title_position);
	$can_display_title = apply_filters("waboot/entry/title/display_flag",$can_display_title,$current_title_position);

    if(!$can_display_title) return;

	//Detecting template (here we prefer these many if statement because they are more readable):
	if($type == "list"){
		$tpl = "templates/view-parts/entry-title.php";
	}else{
		$tpl = "templates/view-parts/entry-title.php"; //starting as list
		if(is_singular() || $type == "single"){
			$tpl = "templates/view-parts/entry-title-singular.php";
		}
		if(\WBF\components\utils\Utilities::get_current_page_type() == Utilities::PAGE_TYPE_BLOG_PAGE && $type == "single"){
			$tpl = "templates/view-parts/entry-title-singular.php";
		}
		if(is_archive()){
			$tpl = "templates/view-parts/entry-title-singular.php";
		}
	}

    $tpl = apply_filters("waboot/entry/title/tpl",$tpl,$current_title_position);
	$tpl_args = [
		'title' => $title
	];
	$tpl_args = apply_filters("waboot/entry/title/tpl_args",$tpl_args);

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
	echo '<footer class="entry-footer">';
}

/**
 * Prints out the entry footer wrapper end
 */
function entry_footer_wrapper_end(){
	echo '</footer>';
}