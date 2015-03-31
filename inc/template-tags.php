<?php

if ( !function_exists("images_url") ) :
    function images_url(){
        echo get_images_url();
    }
endif;

if ( !function_exists("get_images_url") ) :
    function get_images_url(){
        $base_dir = get_template_directory_uri();
        if(is_child_theme()){
            $base_dir = get_stylesheet_directory_uri();
        }
        return apply_filters("waboot_images_url",$base_dir."/assets/images");
    }
endif;

if ( ! function_exists( 'waboot_content_nav' ) ):
	/**
	 * Display navigation to next/previous pages when applicable
	 * @from Waboot
	 */
	function waboot_content_nav( $nav_id, $show_pagination = false, $query = false ) {
		// Return early if theme options are set to hide nav
		if ( 'nav-below' == $nav_id && ! of_get_option( 'waboot_content_nav_below', 1 ) || 'nav-above' == $nav_id && ! of_get_option( 'waboot_content_nav_above' ) ) return;

		if(!$query){
			global $wp_query;
			$query = $wp_query;
		}else{
			if(!$query instanceof WP_Query){
				return; // Return early if query is invalid
			}
		}

		$nav_class = 'site-navigation paging-navigation';
		if(is_single()){
			$nav_class .= ' post-navigation';
		}else{
			$nav_class .= ' paging-navigation';
		}
		?>
		<nav id="<?php echo $nav_id; ?>" class="<?php echo $nav_class; ?>">
			<ul class="pagination">
				<?php
				/*
				 * Navigation links for single posts
				 */
				if(is_single()) :
					previous_post_link( '<li class="previous">%link</li>', '<span class="meta-nav">' . _x( '&laquo;', 'Previous post link', 'waboot' ) . '</span> %title' );
					next_post_link( '<li class="next">%link</li>', '%title <span class="meta-nav">' . _x( '&raquo;', 'Next post link', 'waboot' ) . '</span>' );
				elseif($query->max_num_pages > 1 && (is_home() || is_archive() || is_search() || is_singular())) : // navigation links for home, archive, search pages and common pages
					/*
					 * Navigation links for home, archive, and search pages
					 */
					if($show_pagination){
						$big = 999999999; // need an unlikely integer
						$paginate = paginate_links(array(
							'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
							'format' => '?paged=%#%',
							'current' => max( 1, get_query_var('paged') ),
							'total' => $query->max_num_pages
						));

						$paginate_array = explode("\n",$paginate);
						foreach($paginate_array as $k => $link){
							$paginate_array[$k] = "<li>".$link."</li>";
						}

						$paginate = implode("\n",$paginate_array);

						echo $paginate;
					}else{
						if(get_next_posts_link()) : ?>
							<li class="pull-right"><?php next_posts_link( __( 'Next page <span class="meta-nav">&raquo;</span>', 'waboot' ), $query->max_num_pages ); ?></li>
						<?php endif;

						if(get_previous_posts_link()) : ?>
							<li class="pull-left"><?php previous_posts_link( __( '<span class="meta-nav">&laquo;</span> Previous page', 'waboot' ), $query->max_num_pages ); ?></li>
						<?php endif;
					}
				endif;
				?>
			</ul>
		</nav><!-- #<?php echo $nav_id; ?> -->
	<?php
	}
endif; // waboot_content_nav

if(!function_exists("waboot_archive_page_title")):
	function waboot_archive_page_title($prefix = "", $suffix = "", $display = true){
		if (of_get_option('waboot_blogpage_displaytitle') == "1") {
			$output = waboot_get_archive_page_title();
			$output = $prefix.$output.$suffix;
		}else{
			$output = "";
		}
		if($display){
			echo $output;
		}
		return $output;
	}
endif;

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

if(!function_exists("waboot_get_archive_page_title")):
	function waboot_get_archive_page_title(){
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
endif;

if ( ! function_exists( 'waboot_archive_sticky_posts' ) ):
    /**
     * Display sticky posts on archive pages
     * @since 0.1.0
     * @param string $blog_style
     */
	function waboot_archive_sticky_posts($blog_style = "classic") {

        $sticky = get_option( 'sticky_posts' );
        if ( ! empty( $sticky ) ) {
            global $do_not_duplicate, $page, $paged;
            $do_not_duplicate = array();

            if ( is_category() ) {
                $cat_ID = get_query_var( 'cat' );
                $sticky_args = array(
                    'post__in'    => $sticky,
                    'cat'         => $cat_ID,
                    'post_status' => 'publish',
                    'paged'       => $paged
                );

            } elseif ( is_tag() ) {
                $current_tag = get_queried_object_id();
                $sticky_args = array(
                    'post__in'     => $sticky,
                    'tag_id'       => $current_tag,
                    'post_status'  => 'publish',
                    'paged'        => $paged
                );
            }

            if ( ! empty( $sticky_args ) ):
                $sticky_posts = new WP_Query( $sticky_args );

                if ( $sticky_posts->have_posts() ):
                    global $post;

                    while ( $sticky_posts->have_posts() ) : $sticky_posts->the_post();
                        array_push( $do_not_duplicate, $post->ID );
	                    if($blog_style != "classic"){
		                    get_template_part( '/templates/parts/content', "blog-".$blog_style );
	                    }else{
		                    get_template_part( '/templates/parts/content', get_post_format() );
	                    }
                    endwhile;
                endif; // if have posts
            endif; // if ( ! empty( $sticky_args ) )
        } //if not empty sticky
    }
endif; //waboot_archive_sticky_posts

if ( ! function_exists( 'waboot_comment' ) ) :
    /**
     * Template for comments and pingbacks.
     *
     * Used as a callback by wp_list_comments() for displaying the comments.
     */
    function waboot_comment( $comment, $args, $depth ) {
        $GLOBALS['comment'] = $comment;

        if ( 'pingback' == $comment->comment_type || 'trackback' == $comment->comment_type ) : ?>

            <li id="comment-<?php comment_ID(); ?>" <?php comment_class(); ?>>
            <div class="comment-body">
                <?php _e( 'Pingback:', 'waboot' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( __( 'Edit', 'waboot' ), '<span class="edit-link">', '</span>' ); ?>
            </div>

        <?php else : ?>

        <li id="comment-<?php comment_ID(); ?>" <?php comment_class( empty( $args['has_children'] ) ? '' : 'parent' ); ?>>
            <article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
                <footer class="comment-meta">
                    <div class="comment-author vcard">
                        <?php if ( 0 != $args['avatar_size'] ) echo get_avatar( $comment, $args['avatar_size'] ); ?>
                        <?php printf( __( '%s <span class="says">says:</span>', 'waboot' ), sprintf( '<cite class="fn">%s</cite>', get_comment_author_link() ) ); ?>
                    </div><!-- .comment-author -->

                    <div class="comment-metadata">
                        <a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
                            <time datetime="<?php comment_time( 'c' ); ?>">
                                <?php printf( _x( '%1$s at %2$s', '1: date, 2: time', 'waboot' ), get_comment_date(), get_comment_time() ); ?>
                            </time>
                        </a>
                        <?php edit_comment_link( __( 'Edit', 'waboot' ), '<span class="edit-link">', '</span>' ); ?>
                    </div><!-- .comment-metadata -->

                    <?php if ( '0' == $comment->comment_approved ) : ?>
                        <p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'waboot' ); ?></p>
                    <?php endif; ?>
                </footer><!-- .comment-meta -->

                <div class="comment-content">
                    <?php comment_text(); ?>
                </div><!-- .comment-content -->

                <?php
                comment_reply_link( array_merge( $args, array(
                    'add_below' => 'div-comment',
                    'depth'     => $depth,
                    'max_depth' => $args['max_depth'],
                    'before'    => '<div class="reply">',
                    'after'     => '</div>',
                ) ) );
                ?>
            </article><!-- .comment-body -->

        <?php
        endif;
    }
endif; // ends check for waboot_comment()

function waboot_has_sidebar($prefix){
    $has_sidebar = false;
    for ($i = 1; $i <= 4; $i++) {
        if (is_active_sidebar($prefix . "-" . $i)) {
            $has_sidebar = true;
        }
    }
    return $has_sidebar;
}

/**
 * Determines the theme layout and active sidebars, and prints the HTML structure
 * with appropriate grid classes depending on which are activated.
 *
 * @since 0.1.0
 * @uses waboot_sidebar_class()
 * @param string $prefix Prefix of the widget to be displayed. Example: "footer" for footer-1, footer-2, etc.
 */
function waboot_do_sidebar( $prefix = false ) {
    if ( ! $prefix )
        _doing_it_wrong( __FUNCTION__, __( 'You must specify a prefix when using waboot_do_sidebar.', 'waboot' ), '1.0' );

        // Get our grid class
        $sidebar_class = waboot_sidebar_class( $prefix );

        if ( $sidebar_class ): ?>

            <div class="<?php echo $prefix; ?>-sidebar-row row">
                <?php do_action( 'waboot_sidebar_row_top' );

                if ( is_active_sidebar( $prefix.'-1' ) ): ?>
                    <aside id="<?php echo $prefix; ?>-sidebar-1" class="sidebar widget<?php echo $sidebar_class; ?>">
                        <?php dynamic_sidebar( $prefix.'-1' ); ?>
                    </aside>
                <?php endif;


                if ( is_active_sidebar( $prefix.'-2' ) ): ?>
                    <aside id="<?php echo $prefix; ?>-sidebar-2" class="sidebar widget<?php echo $sidebar_class; ?>">
                        <?php dynamic_sidebar( $prefix.'-2' ); ?>
                    </aside>
                <?php endif;


                if ( is_active_sidebar( $prefix.'-3' ) ): ?>
                    <aside id="<?php echo $prefix; ?>-sidebar-3" class="sidebar widget<?php echo $sidebar_class; ?>">
                        <?php dynamic_sidebar( $prefix.'-3' ); ?>
                    </aside>
                <?php endif;


                if ( is_active_sidebar( $prefix.'-4' ) ): ?>
                    <aside id="<?php echo $prefix; ?>-sidebar-4" class="sidebar widget<?php echo $sidebar_class; ?>">
                        <?php dynamic_sidebar( $prefix.'-4' ); ?>
                    </aside>
                <?php endif;

                do_action( 'waboot_sidebar_row_bottom' ); ?>
            </div><!-- .row -->

        <?php endif; //$sidebar_class
}

/**
 * Count the number of active widgets to determine dynamic wrapper class
 * @since 0.1.0
 */
function waboot_sidebar_class( $prefix = false ) {
    if ( ! $prefix )
        _doing_it_wrong( __FUNCTION__, __( 'You must specify a prefix when using waboot_sidebar_class.', 'waboot' ), '1.0' );

    $count = 0;

    if ( is_active_sidebar( $prefix.'-1' ) )
        $count++;

    if ( is_active_sidebar( $prefix.'-2' ) )
        $count++;

    if ( is_active_sidebar( $prefix.'-3' ) )
        $count++;

    if ( is_active_sidebar( $prefix.'-4' ) )
        $count++;

    $class = '';

    switch ( $count ) {
        case '1':
            $class = ' col-sm-12';
            break;

        case '2':
            $class = ' col-sm-6';
            break;

        case '3':
            $class = ' col-sm-4';
            break;

        case '4':
            $class = ' col-sm-3';
            break;
    }

    if ( $class )
        return $class;
}

/* Post Format Gallery */
if ( ! function_exists( 'waboot_gallery_format' ) ) :
    function waboot_gallery_format() {
        $output = $images_ids = '';

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

        return $output;
    }
endif;
/* End Post Format Gallery */


/* Post Format Video */
function waboot_video_embed_html( $video ) {
    return "<div class='wb_post_video'>{$video}</div>";
}
add_filter( 'embed_oembed_html', 'waboot_video_embed_html' );

if ( ! function_exists( 'waboot_get_first_video' ) ) :
    function waboot_get_first_video() {
        $first_oembed  = '';
        $custom_fields = get_post_custom();

        foreach ( $custom_fields as $key => $custom_field ) {
            if ( 0 !== strpos( $key, '_oembed_' ) ) continue;

            $first_oembed = $custom_field[0];

            $video_width  = (int) apply_filters( 'wb_video_width', 1132 );
            $video_height = (int) apply_filters( 'wb_video_height', 480 );

            $first_oembed = preg_replace( '/<embed /', '<embed wmode="transparent" ', $first_oembed );
            $first_oembed = preg_replace( '/<\/object>/','<param name="wmode" value="transparent" /></object>', $first_oembed );

            $first_oembed = preg_replace( "/width=\"[0-9]*\"/", "width={$video_width}", $first_oembed );
            $first_oembed = preg_replace( "/height=\"[0-9]*\"/", "height={$video_height}", $first_oembed );

            break;
        }

        return ( '' !== $first_oembed ) ? $first_oembed : false;
    }
endif;
/* End Post Format Video */

if ( ! function_exists( 'waboot_the_trimmed_excerpt' ) ) :
	/**
	 * A version of the_excerpt() that applies the trim function to the predefined excerpt as well
	 * @param bool $length
	 * @param bool|null $more
	 */
	function waboot_the_trimmed_excerpt($length = false,$more = null){
		if(!$length){
			$excerpt_length = apply_filters( 'excerpt_length', 55 );
		}else{
			$excerpt_length = $length;
		}
		if(is_null($more)){
			$excerpt_more = apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );
		}else{
			$excerpt_more = $more;
		}
		echo  wp_trim_words(get_the_excerpt(),$excerpt_length,$excerpt_more);
	}
endif;

/* Post Format Link */
if ( ! function_exists( 'waboot_link_format_helper' ) ) :
    /**
     * Returns the first post link and/or post content without the link.
     * Used for the "Link" post format.
     *
     * @since 0.1.0
     * @param string $output "link" or "post_content"
     * @return string Link or Post Content without link.
     */
    function waboot_link_format_helper( $output = false ) {

        if ( ! $output )
            _doing_it_wrong( __FUNCTION__, __( 'You must specify the output you want - either "link" or "post_content".', 'waboot' ), '1.0.1' );

        $post_content = get_the_content();

        $link = preg_match( '/<a\s[^>]*?href=[\'"](.+?)[\'"][^>]*>[^>]*>/is', $post_content, $matches );
        if($link){
            $link_url = $matches[1];
            $post_content = substr( $post_content, strlen( $matches[0] ) );
            if(!$post_content) $post_content = "";
        }

        // Return the first link in the post content
        if ( 'link' == $output ){
            if($link){
                return $link_url;
            }else{
                return "";
            }
        }

        // Return the post content without the first link
        if ( 'post_content' == $output )
            return $post_content;
    }
endif;
/* End Post Format Link */


if ( ! function_exists( 'waboot_the_attached_image' ) ) :
    /**
     * Prints the attached image with a link to the next attached image.
     */
    function waboot_the_attached_image() {

        $post                = get_post();
        $attachment_size     = apply_filters( 'waboot_attachment_size', array( 1200, 1200 ) );
        $next_attachment_url = wp_get_attachment_url();

        /**
         * Grab the IDs of all the image attachments in a gallery so we can get the
         * URL of the next adjacent image in a gallery, or the first image (if
         * we're looking at the last image in a gallery), or, in a gallery of one,
         * just the link to that image file.
         */
        $attachment_ids = get_posts( array(
            'post_parent'    => $post->post_parent,
            'fields'         => 'ids',
            'numberposts'    => -1,
            'post_status'    => 'inherit',
            'post_type'      => 'attachment',
            'post_mime_type' => 'image',
            'order'          => 'ASC',
            'orderby'        => 'menu_order ID'
        ) );

        // If there is more than 1 attachment in a gallery...
        if ( count( $attachment_ids ) > 1 ) {
            foreach ( $attachment_ids as $attachment_id ) {
                if ( $attachment_id == $post->ID ) {
                    $next_id = current( $attachment_ids );
                    break;
                }
            }

            // get the URL of the next image attachment...
            if ( $next_id )
                $next_attachment_url = get_attachment_link( $next_id );

            // or get the URL of the first image attachment.
            else
                $next_attachment_url = get_attachment_link( array_shift( $attachment_ids ) );
        }

        printf( '<a href="%1$s" title="%2$s" rel="attachment">%3$s</a>',
            esc_url( $next_attachment_url ),
            the_title_attribute( array( 'echo' => false ) ),
            wp_get_attachment_image( $post->ID, $attachment_size )
        );
    }
endif;

if ( ! function_exists( 'waboot_archive_get_posts' ) ):
    /**
     * Display archive posts and exclude sticky posts
     * @since 0.1.0
     * @unused
     */
    function waboot_archive_get_posts() {

        global $do_not_duplicate, $page, $paged;

        if ( is_category() ) {
            $cat_ID = get_query_var( 'cat' );
            $args = array(
                'cat'                 => $cat_ID,
                'post_status'         => 'publish',
                'post__not_in'        => array_merge( $do_not_duplicate, get_option( 'sticky_posts' ) ),
                'ignore_sticky_posts' => 1,
                'paged'               => $paged
            );
            $wp_query = new WP_Query( $args );

        } elseif (is_tag() ) {
            $current_tag = single_tag_title( "", false );
            $args = array(
                'tag_slug__in'        => array( $current_tag ),
                'post_status'         => 'publish',
                'post__not_in'        => array_merge( $do_not_duplicate, get_option( 'sticky_posts' ) ),
                'ignore_sticky_posts' => 1,
                'paged'               => $paged
            );
            $wp_query = new WP_Query( $args );

        } else {
            new WP_Query();
        }
    }
endif;

if (!function_exists("waboot_breadcrumb")):
    /**
     * Display the breadcrumb for $post_id or global $post->ID
     * @param null $post_id
     * @param string $current_location the current location of breadcrumb. Not used at the moment.
     * @param array $args settings for breadcrumb (see: waboot_breadcrumb_trail() documentation)
     * @since 0.3.10
     */
    function waboot_breadcrumb($post_id = null, $current_location = "", $args = array()) {
        global $post;
        if (function_exists('waboot_breadcrumb_trail')) {
            if(is_front_page() || is_404()) return;

	        $args = wp_parse_args($args, array(
		        'container' => "div",
		        'separator' => "/",
		        'show_browse' => false,
		        'additional_classes' => ""
	        ));

	        if(!is_archive() && !is_search()){
		        $post_id = isset($post_id) ? $post_id : $post->ID;
		        $current_post_type = get_post_type($post_id);
		        if (!isset($post_id) || $post_id == 0 || !$current_post_type) return;
		        $bc_locations = of_get_option('waboot_breadcrumb_locations', array('post', 'page'));
		        if (array_key_exists($current_post_type, $bc_locations) && $bc_locations[$current_post_type] == 1) {
			        waboot_breadcrumb_trail($args);
		        }
	        }else{
		        $bc_locations = of_get_option('waboot_breadcrumb_locations', array('archive', 'tag', 'tax'));
		        $show_bc = false;
		        if(is_tag() && in_array('tag',$bc_locations)){
			        $show_bc = true;
		        }elseif(is_tax() && in_array('tax',$bc_locations)){
			        $show_bc = true;
		        }elseif(is_archive() && in_array('archive',$bc_locations) ){
			        $show_bc = true;
		        }

		        if($show_bc) waboot_breadcrumb_trail($args);
	        }
        }
    }
endif;

if (!function_exists("waboot_topnav_wrapper")):
    function waboot_topnav_wrapper(){
        $social_position = of_get_option('waboot_social_position');
        $social_position_class = $social_position == "topnav-left" ? "pull-left" : "pull-right";
        $topnavmenu_position = of_get_option('waboot_topnavmenu_position');
        $topnavmenu_position_class = $topnavmenu_position == "left" ? "pull-left" : "pull-right";
        $has_menu = has_nav_menu('top');
        $must_display_topnav = (is_active_sidebar('topbar') || ($social_position == 'topnav-right' || $social_position == 'topnav-left') || $has_menu) ? true : false;

        if ($must_display_topnav):
            ?>
            <!-- Navbar: Begin -->
            <div id="topnav-wrapper">
                <div id="topnav-inner" class="<?php echo of_get_option('waboot_topnav_width', 'container-fluid'); ?> ">

                    <div class="<?php echo $social_position_class; ?>">
                        <?php get_template_part('/templates/parts/social-widget'); ?>
                    </div>

                    <div class="<?php echo $topnavmenu_position_class; ?>">
                        <?php if ($has_menu) get_template_part('/templates/parts/menu', 'top'); ?>
                    </div>

                    <?php dynamic_sidebar('topbar'); ?>
                </div>
            </div>
            <!-- Navbar: End -->
        <?php
        endif;
    }
endif;

if(!function_exists( "waboot_get_body_layout" )):
    function waboot_get_body_layout(){
        if(is_home() || is_archive()){
            return of_get_option('waboot_blogpage_sidebar_layout');
        }else{
            return get_behavior('layout');
        }
    }
endif;

if(!function_exists( "waboot_body_layout_has_two_sidebars" )):
	function waboot_body_layout_has_two_sidebars(){
		$body_layout = waboot_get_body_layout();
		if(in_array($body_layout,array("two-sidebars","two-sidebars-right","two-sidebars-left"))){
			return true;
		}else{
			return false;
		}
	}
endif;

if(!function_exists( "waboot_get_available_body_layouts" )){
    function waboot_get_available_body_layouts(){
        $imagepath = get_template_directory_uri() . '/wbf/admin/images/';
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

if(!function_exists("waboot_get_compiled_stylesheet_name")):
	function waboot_get_compiled_stylesheet_name(){
		/*$theme = wp_get_theme()->stylesheet;
		if($theme == "wship") $theme = "waboot"; //Brutal compatibility hack :)*/
		if(is_child_theme()){
			return "waboot-child";
		}else{
			return "waboot";
		}
	}
endif;

if(!function_exists("waboot_get_uri_path_after")):
	/**
	 * Get the uri parts after specified tag. Eg: if the uri is "/foo/bar/zor/", calling waboot_get_uri_path_after(foo) will return: array("bar","zor")
	 * @param $tag
	 * @return array
	 */
	function waboot_get_uri_path_after($tag){
		$url_parts = parse_url("http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]);
		$path_parts = explode("/",$url_parts['path']);
		$key = 0;
		foreach($path_parts as $k => $p){
			if($p == $tag){
				$key = $k;
			}
		}
		$path_parts_sliced = array_slice($path_parts,(int)$key+1);
		return $path_parts_sliced;
	}
endif;

// ###############################
// ###############################
// CATEGORIES AND TAXONOMY HELPERS
// ###############################
// ###############################

if ( ! function_exists( 'waboot_get_the_category' ) ) :
	/**
	 * Get the post categories ordered by ID. If the post is a custom post type it retrieve the specified $taxonomy terms or the first registered taxonomy
	 * @param null $post_id
	 * @param null $taxonomy the taxonomy to retrieve if the POST is a custom post type
	 * @param bool $ids_only retrieve only the ID of the categories
	 * @internal param null $the_post
	 * @return array
	 */
	function waboot_get_the_category($post_id = null, $taxonomy = null, $ids_only = false){
		if(!isset($post_id)){
			global $post;
			$post_id = $post->ID;
		}else{
			$post = get_post($post_id);
		}

		if(get_post_type($post_id) == "post"){
			$terms = get_the_category($post_id);
			if($ids_only){
				foreach($terms as $id => $term){
					$categories[] = $id;
				}
			}else{
				$categories = $terms;
			}
		}else{
			if(!isset($taxonomy)){
				$terms = get_the_terms($post_id,waboot_get_first_taxonomy($post_id));
				if($ids_only){
					foreach($terms as $id => $term){
						$categories[] = $id;
					}
				}else{
					$categories = $terms;
				}
			}else{
				if($ids_only){
					$categories = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );
				}else{
					$categories = wp_get_object_terms( $post_id, $taxonomy);
				}
			}
		}

		if($ids_only){
			if(isset($categories) && is_array($categories))
				sort($categories,SORT_NUMERIC);
		}else{
			if(isset($categories) && is_array($categories))
				usort($categories,"waboot_sort_categories_by_id");
		}

		return $categories;
	}
endif;

if ( ! function_exists( 'waboot_get_top_categories' ) ) :
	/**
	 * Get the top level categories
	 * @param null $taxonomy
	 * @return array
	 */
	function waboot_get_top_categories($taxonomy = null){
		if(!$taxonomy){
			$cats = get_categories();
		}else{
			$cats = get_categories(array(
				'taxonomy' => $taxonomy
			));
		}

		$top_cat_obj = array();

		foreach($cats as $cat) {
			if ($cat->parent == 0) {
				$top_cat_obj[] = $cat;
			}
		}

		return $top_cat_obj;
	}
endif;

if ( ! function_exists( 'waboot_get_top_category' ) ) :
	/**
	 * Gets top level category of the current or specified post
	 * @param string $return_value "id" or "slug". If empty the category object is returned.
	 * @return string|object
	 */
	function waboot_get_top_category($return_value = "", $post_id = null) {
		if(!$post_id)
			$cats = waboot_get_the_category(); // category object
		else
			$cats = waboot_get_the_category($post_id); // category object

		if(!$cats) return false;

		$top_cat_obj = array();

		foreach($cats as $cat) {
			if ($cat->parent == 0) {
				$top_cat_obj[] = $cat;
			}
		}

		if(!isset($top_cat_obj[0])){
			$top_cat_obj = $cats[0];
		}else{
			$top_cat_obj = $top_cat_obj[0];
		}

		if($return_value == ""){
			return $top_cat_obj;
		}else{
			switch($return_value){
				case "id":
					return $top_cat_obj->term_id;
					break;
				case "slug":
					return $top_cat_obj->slug;
					break;
				default:
					return $top_cat_obj;
					break;
			}
		}
	}
endif;

if ( ! function_exists( 'waboot_get_first_taxonomy' ) ) :
	/**
	 * Get the first registered taxonomy of a custom post type
	 * @param null $post_id
	 * @return string
	 */
	function waboot_get_first_taxonomy($post_id = null){
		if(!isset($post_id)){
			global $post;
			$post_id = $post->ID;
		}else{
			$post = get_post($post_id);
		}

		if(get_post_type($post_id) == "post"){
			return 'category';
		}else{
			$post_type_taxonomies = get_object_taxonomies($post->post_type);
			return $post_type_taxonomies[0];
		}
	}
endif;

if ( ! function_exists( 'waboot_sort_categories_by_id' ) ) :
	/**
	 * Sort the categories of a post by ID (ASC)
	 * @param $a
	 * @param $b
	 * @return int
	 */
	function waboot_sort_categories_by_id($a,$b){
		if((int)$a->term_id == (int)$b->term_id) return 0;
		return (int)$a->term_id < (int)$b->term_id ? -1 : 1;
	}
endif;