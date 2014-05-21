<?php
/**
 * Custom template tags for this theme.
 *
 * @package Alien Ship
 * @since 0.1
 */


/**
 * Count the number of active widgets to determine dynamic wrapper class
 *
 * @since 1.0
 */
function alienship_sidebar_class( $prefix = false ) {

	if ( ! $prefix )
		_doing_it_wrong( __FUNCTION__, __( 'You must specify a prefix when using alienship_sidebar_class.', 'alienship' ), '1.0' );

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

/**
 * Determines the theme layout and active sidebars, and prints the HTML structure
 * with appropriate grid classes depending on which are activated.
 *
 * @since 1.0
 * @uses alienship_sidebar_class()
 * @param string $prefix Prefix of the widget to be displayed. Example: "footer" for footer-1, footer-2, etc.
 */
function alienship_do_sidebar( $prefix = false ) {

	if ( ! $prefix )
		_doing_it_wrong( __FUNCTION__, __( 'You must specify a prefix when using alienship_do_sidebar.', 'alienship' ), '1.0' );


	if ( current_theme_supports( 'theme-layouts' ) && !is_admin() && 'layout-1c' !== theme_layouts_get_layout() || !current_theme_supports( 'theme-layouts' ) ):

		// Get our grid class
		$sidebar_class = alienship_sidebar_class( $prefix );

		if ( $sidebar_class ): ?>

			<div class="<?php echo $prefix; ?>-sidebar-row row">
				<?php do_action( 'alienship_sidebar_row_top' );

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

				do_action( 'alienship_sidebar_row_bottom' ); ?>
			</div><!-- .row -->

		<?php endif; //$sidebar_class

	endif; //current_theme_supports
}

if ( ! function_exists( 'alienship_archive_get_posts' ) ):
/**
 * Display archive posts and exclude sticky posts
 * @since .594
 */
function alienship_archive_get_posts() {

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



if ( ! function_exists( 'alienship_get_first_link' ) ):
/**
 * Get the first link in a post
 * Used to link the title to external links on the "Link" post format
 * @since .64
 * @deprecated since 1.0.1. Use 'alienship_link_format_helper' instead.
 */
function alienship_get_first_link() {

	_deprecated_function( __FUNCTION__, '1.0.1', 'alienship_link_format_helper()' );

	global $link_url, $post_content;
	$content = get_the_content();
	$link_start = stristr( $content, "http" );
	$link_end = stristr( $link_start, "\n" );

	if ( ! strlen( $link_end ) == 0 ) {
		$link_url = substr( $link_start, 0, -( strlen( $link_end ) + 1 ) );
	} else {
		$link_url = $link_start;
	}

	$post_content = substr( $content, strlen( $link_url ) );
}
endif;



if ( ! function_exists( 'alienship_link_format_helper' ) ) :
/**
 * Returns the first post link and/or post content without the link.
 * Used for the "Link" post format.
 *
 * @since 1.0.1
 * @param string $output "link" or "post_content"
 * @return string Link or Post Content without link.
 */
function alienship_link_format_helper( $output = false ) {

	if ( ! $output )
		_doing_it_wrong( __FUNCTION__, __( 'You must specify the output you want - either "link" or "post_content".', 'alienship' ), '1.0.1' );

	$post_content = get_the_content();
	$link_start = stristr( $post_content, "http" );
	$link_end = stristr( $link_start, "\n" );

	if ( ! strlen( $link_end ) == 0 ) {
		$link_url = substr( $link_start, 0, -( strlen( $link_end ) + 1 ) );
	} else {
		$link_url = $link_start;
	}

	$post_content = substr( $post_content, strlen( $link_url ) );

	// Return the first link in the post content
	if ( 'link' == $output )
		return $link_url;

	// Return the post content without the first link
	if ( 'post_content' == $output )
		return $post_content;
}
endif;



if ( ! function_exists( 'alienship_the_attached_image' ) ) :
/**
 * Prints the attached image with a link to the next attached image.
 */
function alienship_the_attached_image() {

	$post                = get_post();
	$attachment_size     = apply_filters( 'alienship_attachment_size', array( 1200, 1200 ) );
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

if ( ! function_exists( 'alienship_get_header_image' ) ):
    /**
     * Returns header image and accompanying markup
     *
     * @since 1.1.1
     * @return array $header_image_attributes (filtered) Header image attributes
     */
    function alienship_get_header_image() {

        global $post;
        $output = '';

        // Get the header image
        if ( get_header_image() ) {

            $header_image_width = get_theme_support( 'custom-header', 'width' );
            $header_image_height = get_theme_support( 'custom-header', 'height' );

            $output = '<a href="' . esc_url( home_url( '/' ) ) . '">';

            // Check if this is a post or page, if it has a thumbnail, and if it's a big one
            if ( is_singular() && has_post_thumbnail( $post->ID )
                /* $src, $width, $height */
                && ( $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), array( $header_image_width, $header_image_height ) ) )
                && $image[1] >= $header_image_width ) {

                // We have a LARGE image
                $featured_header_image = 'yes';
                $output .= get_the_post_thumbnail( $post->ID, 'post-thumbnail' );

            } else {

                $featured_header_image = 'no';
                $header_image_width  = get_custom_header()->width;
                $header_image_height = get_custom_header()->height;
                $output .= '<img src="' . get_header_image() . '" width="' . $header_image_width . '" height="' . $header_image_height . '" class="header-image" alt="">';
            }
            $output .= '</a>';

            $header_image_attributes = array(
                'width'    => $header_image_width,
                'height'   => $header_image_height,
                'featured' => $featured_header_image,
                'output'   => $output,
            );

            return apply_filters( 'alienship_header_image_attributes', $header_image_attributes );

        }

    }
endif;

if( ! function_exists( 'alienship_do_header_image' ) ):
    /**
     * Echoes the header image and accompanying markup
     *
     * @since 1.1.1
     */
    function alienship_do_header_image() {

        $output = alienship_get_header_image();
        if ( $output )
            echo apply_filters( 'alienship_header_image_output', $output['output'] );
    }
    add_action( 'alienship_header_image', 'alienship_do_header_image' );
endif;
