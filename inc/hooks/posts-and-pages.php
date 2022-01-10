<?php

namespace Waboot\inc\hooks;

use Waboot\inc\core\utils\Utilities;
use function Waboot\inc\getArchivePageTitle;
use function Waboot\inc\getIndexPageTitle;
use function Waboot\inc\getTheTermsListHierarchical;
use function Waboot\inc\core\Waboot;

add_action('waboot/layout/title',__NAMESPACE__."\\displayTitle");
add_action('waboot/layout/title/after',__NAMESPACE__."\\displayTaxonomyDescription",20);

//Article Footer:
add_action('waboot/article/footer',__NAMESPACE__."\\articleFooterWrapperStart",10);
add_action('waboot/article/footer',__NAMESPACE__."\\displayPostDate",20);
add_action('waboot/article/footer',__NAMESPACE__."\\displayPostAuthor",30);
add_action('waboot/article/footer',__NAMESPACE__."\\displayPostCategories",40);
add_action('waboot/article/footer',__NAMESPACE__."\\displayPostTags",50);
add_action('waboot/article/footer',__NAMESPACE__."\\displayPostCommentLink",60);
add_action('waboot/article/footer',__NAMESPACE__."\\articleFooterWrapperEnd",9999);

/**
 * Display the main title of page
 */
function displayTitle(){
    global $post;
    $page_type = Utilities::getCurrentPageType();

    $can_display_title = true;

    if($page_type === Utilities::PAGE_TYPE_DEFAULT_HOME || $page_type === Utilities::PAGE_TYPE_BLOG_PAGE){
        $title = getIndexPageTitle();
        if(!isset($title)){
            //We are in the "default_home" case
            $title = apply_filters('waboot/blog/title','');
        }
    }elseif(is_search()){
        $title = sprintf( __( 'Search Results for: %s', LANG_TEXTDOMAIN ), '<span>' . get_search_query() . '</span>' );
    }elseif(is_archive()){
        $title = getArchivePageTitle();
    }elseif(is_singular()){
        $title = get_the_title($post->ID);
    }

    if(!isset($title)){
        $title = $post instanceof \WP_Post ? get_the_title($post->ID) : '';
    }

    $title = apply_filters('waboot/main/title',$title,$post,Utilities::getCurrentPageType());
    $can_display_title = apply_filters('waboot/main/title/display_flag',$can_display_title,$post,Utilities::getCurrentPageType());

    if(!$can_display_title) return;

    $tpl = 'templates/view-parts/main-title.php';

    $tpl_args = [
        'title' => $title,
        'classes' => ''
    ];

    if(is_archive()){
        $tpl_args['classes'] = 'main__title--archive';
    }

    $tpl_args['title_context'] = Utilities::getCurrentPageType();

    $tpl = apply_filters('waboot/main/title/tpl',$tpl,Utilities::getCurrentPageType());
    $tpl_args = apply_filters('waboot/main/title/tpl_args',$tpl_args);

    Waboot()->renderView($tpl,$tpl_args);
}


/**
 * Prints HTML with date posted information for the current post.
 *
 * @param null $print_relative
 */
function displayPostDate($print_relative = null){
	$tpl = 'templates/view-parts/entry-date.php';

    if(!isset($print_relative)){
        $relative_time = \Waboot\functions\get_option('show_post_relative_time', false);
    }else{
        $relative_time = $print_relative;
    }

    Waboot()->renderView($tpl,[
        'tag_date' => esc_attr(get_the_date('c')),
        'date' => !$relative_time ? esc_html( get_the_date() ) : sprintf( _x( '%s ago', 'Relative date output for entry footer' ,LANG_TEXTDOMAIN ), human_time_diff( get_the_date( 'U' ), current_time( 'timestamp' ) ) )
    ]);
}

/**
 * Prints HTML with meta information for the current post's author.
 */
function displayPostAuthor(){
	$tpl = 'templates/view-parts/entry-author.php';
	Waboot()->renderView($tpl,[
        'author_url' =>  esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
        'author_title' => esc_attr(sprintf(__( 'View all posts by %s', LANG_TEXTDOMAIN ), get_the_author())),
        'author_name' => esc_html(get_the_author())
    ]);
}

/**
 * Display the list of categories on a post in hierarchical order
 */
function displayPostCategories(){
	echo getTheTermsListHierarchical(get_the_ID(), 'category', '
    <span class="article__categories"><svg
		width="18"
		height="18"
		fill="none"
		stroke="currentColor"
		stroke-width="1.5"
		stroke-linecap="round"
		stroke-linejoin="round"
	>
		<use href="' . get_template_directory_uri() . '/assets/images/default/icons/feather-sprite.svg#folder
"/><span class="cat-links">', ', ', '</span></span>');
}

/**
 * Display the list of post tags
 */
function displayPostTags(){
	$post_tags = get_the_tags();

	if(is_array($post_tags) && !empty($post_tags)){
		$wrapper_start = '<span class="tags-links"><svg
        width="18"
        height="18"
        fill="none"
        stroke="currentColor"
        stroke-width="1.5"
        stroke-linecap="round"
        stroke-linejoin="round"
    >
        <use href="' . get_template_directory_uri() . '/assets/images/default/icons/feather-sprite.svg#tag"/>
    </svg>';
		$wrapper_end = '</span>';
		$num_tags = count( $post_tags );

		echo $wrapper_start;

		$tag_count = 1;
		foreach( $post_tags as $tag ) {
			$html_before = '<a href="%s" rel="tag nofollow" class="tag-text">';
			$html_after = '</a>';
			$sep = $tag_count < $num_tags ? ', ' : '';

			echo sprintf($html_before,get_tag_link($tag->term_id)) . $tag->name . $html_after . $sep;

			$tag_count++;
		}

		echo $wrapper_end;
	}
}

/**
 * Display the "leave comment" link.
 */
function displayPostCommentLink(){
	$tpl = 'templates/view-parts/entry-comment-link.php';

	Waboot()->renderView($tpl);
}

/**
 * Prints out the entry footer wrapper start
 */
function articleFooterWrapperStart(){
	echo '<div class="article__footer">';
}

/**
 * Prints out the entry footer wrapper end
 */
function articleFooterWrapperEnd(){
	echo '</div>';
}

/**
 * Prints out the taxonomy descriptions
 */
function displayTaxonomyDescription(){
    if(is_archive()){
        $tpl = 'templates/view-parts/archive-description.php';
        Waboot()->renderView($tpl);
    }
}
