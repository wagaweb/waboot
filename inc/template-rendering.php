<?php

namespace Waboot\inc;

use function Waboot\inc\core\Waboot;/**
 * Display the content navigation
 *
 * @param string $nav_id (you can use 'nav-below' or 'nav-above')
 * @param bool $show_pagination
 * @param bool $query
 * @param bool $current_page
 * @param string $paged_var_name You can supply different paged var name for multiple pagination. The name must be previously registered with add_rewrite_tag()
 *@throws \Exception
 *
 */
function renderPostNavigation($nav_id, $show_pagination = true, $query = false, $current_page = false, $paged_var_name = 'paged'){
    //Setting up the query
    if(!$query){
        global $wp_query;
        $query = $wp_query;
    }else{
        if(!$query instanceof \WP_Query){
            throw new \Exception("Invalid query provided for post_navigation $nav_id");
        }
    }

    if(is_home() || is_archive() || is_search()){
        $show_pagination = true;
        $can_display_pagination = $query->max_num_pages > 1;
    }else{
        $show_pagination = false;
        $can_display_pagination = true;
    }
    $show_pagination = apply_filters('waboot/layout/post_navigation/display_pagination_flag',$show_pagination,$nav_id);

    if(!$show_pagination || !$can_display_pagination){ return; }

    if($can_display_pagination && $show_pagination){
        $big = 999999999; // need an unlikely integer
        if($paged_var_name !== 'paged'){
            $base =  add_query_arg([
                $paged_var_name => "%#%"
            ]);
            $base = home_url().$base;
        }else{
            $base =  str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) );
        }
        if($current_page === false){
            $current_page = 1;
            if($paged_var_name !== 'paged' && isset($_GET[$paged_var_name])){
                $current_page = (int) $_GET[$paged_var_name];
            }else{
                $current_page = max(1, (int) get_query_var('paged'));
            }
        }
        $paginate = paginate_links([
            'base' => $base,
            'format' => '?'.$paged_var_name.'=%#%',
            'current' => $current_page,
            'total' => $query->max_num_pages
        ]);
        $paginate_array = explode("\n",$paginate);
        foreach($paginate_array as $k => $link){
            $paginate_array[$k] = '<li>' .$link. '</li>';
        }
        $pagination = implode("\n",$paginate_array);
    }else{
        $pagination = '';
    }

    Waboot()->renderView('templates/view-parts/pagination.php',[
        'nav_id' => $nav_id,
        'can_display_pagination' => $can_display_pagination,
        'show_pagination' => $show_pagination,
        'pagination' => $pagination,
        'max_num_pages' => $query->max_num_pages
    ],true);
}

/**
 * Display the post gallery
 *
 * @return bool|string
 */
function displayPostGallery() {
    if ( function_exists( 'get_post_galleries' ) ) {
        $galleries = get_post_galleries( get_the_ID(), false );

        if ( empty( $galleries ) ) return false;

        if ( isset( $galleries[0]['ids'] ) ) {
            foreach ( $galleries as $gallery ) {
                // Grabs all attachments ids from one or multiple galleries in the post
                $images_ids .= ( '' !== $images_ids ? ',' : '' ) . $gallery['ids'];
            }

            $attachments_ids = explode( ',', $images_ids );
            // Removes duplicate attachments ids
            $attachments_ids = array_unique( $attachments_ids );
        } else {
            $attachments_ids = get_posts( array(
                'fields'         => 'ids',
                'numberposts'    => 999,
                'order'          => 'ASC',
                'orderby'        => 'menu_order',
                'post_mime_type' => 'image',
                'post_parent'    => get_the_ID(),
                'post_type'      => 'attachment',
            ) );
        }
    } else {
        $pattern = get_shortcode_regex();
        preg_match( "/$pattern/s", get_the_content(), $match );
        $atts = shortcode_parse_atts( $match[3] );

        if ( isset( $atts['ids'] ) )
            $attachments_ids = explode( ',', $atts['ids'] );
        else
            return false;
    }

    echo '<div id="carousel-gallery-format" class="carousel slide" data-ride="carousel">';
    echo '	<div class="carousel-inner" role="listbox">';
    $i = 0;
    foreach ( $attachments_ids as $attachment_id ) {
        if($i == 0){
            printf( '<div class="item active">%s</div>',
                // esc_url( get_permalink() ),
                wp_get_attachment_image( $attachment_id, 'medium' )
            );
        }else{
            printf( '<div class="item">%s</div>',
                // esc_url( get_permalink() ),
                wp_get_attachment_image( $attachment_id, 'medium' )
            );
        }
        $i++;
    }
    echo '	</div> <!-- .carousel-inner -->';
    echo '<!-- Controls -->
          <a class="left carousel-control" href="#carousel-gallery-format" role="button" data-slide="prev">
            <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
          </a>
          <a class="right carousel-control" href="#carousel-gallery-format" role="button" data-slide="next">
            <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
          </a>
    ';
    echo '</div> <!-- #carousel-gallery-format -->';
}

/**
 * @param $areaId
 */
function renderWidgetArea($areaId){
    Waboot()->renderView('templates/view-parts/widget-area.php', ['areaId' => $areaId]);
}

/**
 * Template for comments and pingbacks.
 *
 * Used as a callback by wp_list_comments() for displaying the comments. (@see comments.php)
 */
function renderComment($comment, $args, $depth){
    $vars = [
        'additional_comment_class' => empty( $args['has_children'] ) ? '' : 'parent',
        'is_approved' => $comment->comment_approved  != '0',
        'has_avatar' => $args['avatar_size'] != '0',
        'avatar' => get_avatar( $comment, $args['avatar_size'] ),
        'comment' => $comment,
        'args' => $args,
        'depth' => $depth
    ];

    $template_file = 'templates/view-parts/single-comment.php';

    Waboot()->renderView($template_file,$vars);
}
