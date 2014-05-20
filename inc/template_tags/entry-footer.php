<?php
/**
 * Print the opening markup for the entry footer.
 *
 * @since 1.0
 *
 */
function waboot_entry_footer_markup_open() {
    echo '<footer class="entry-footer">';
}
add_action( 'waboot_entry_footer', 'waboot_entry_footer_markup_open', 5 );

if ( ! function_exists( 'waboot_do_posted_on' ) ) :
    /**
     * Prints HTML with date posted information for the current post.
     *
     * @since 1.0
     */
    function waboot_do_posted_on() {

        // Return early if theme options are set to hide date
        if ( ! of_get_option( 'alienship_published_date', 1 ) )
            return;

        printf( __( '<span class="published-date"><i class="glyphicon glyphicon-calendar" title="Published date"></i> <a href="%1$s" title="%2$s"><time class="entry-date" datetime="%3$s">%4$s</time></a></span>', 'alienship' ),
            esc_url( get_permalink() ),
            esc_attr( get_the_time() ),
            esc_attr( get_the_date( 'c' ) ),
            esc_html( get_the_date() )
        );
    }
    add_action( 'waboot_entry_footer', 'waboot_do_posted_on', 6 );
endif;

if ( ! function_exists( 'waboot_do_post_author' ) ) :
    /**
     * Prints HTML with meta information for the current post's author.
     *
     * @since 1.0
     */
    function waboot_do_post_author() {

        // Return early if theme options are set to hide author
        if ( ! of_get_option('alienship_post_author', 1 ) )
            return;

        printf( __( '<span class="byline"><i class="glyphicon glyphicon-user"></i> <span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></span></span>', 'alienship' ),
            esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
            esc_attr( sprintf( __( 'View all posts by %s', 'alienship' ), get_the_author() ) ),
            esc_html( get_the_author() )
        );
    }
    add_action( 'waboot_entry_footer', 'waboot_do_post_author', 7 );
endif;

if ( ! function_exists( 'waboot_do_post_categories' ) ):
    /**
     * Customize the list of categories displayed on index and on a post
     * @since 1.0
     */
    function waboot_do_post_categories() {

        // Return early if theme options are set to hide categories
        if ( ! of_get_option( 'alienship_post_categories', 1 ) )
            return;

        $post_categories = get_the_category();
        if ( $post_categories ) {

            echo '<span class="cat-links"><i class="glyphicon glyphicon-folder-open" title="Categories"></i> ';
            $num_categories = count( $post_categories );
            $category_count = 1;

            foreach ( $post_categories as $category ) {
                $html_before = '<a href="' . get_category_link( $category->term_id ) . '" class="cat-text">';
                $html_after = '</a>';

                if ( $category_count < $num_categories )
                    $sep = ', ';
                elseif ( $category_count == $num_categories )
                    $sep = '';
                echo $html_before . $category->name . $html_after . $sep;
                $category_count++;
            }
            echo '</span>';
        }
    }
    add_action( 'waboot_entry_footer', 'waboot_do_post_categories', 8 );
endif;

if ( ! function_exists( 'waboot_do_post_tags' ) ):
    /**
     * Customize the list of tags displayed on index and on a post
     * @since 1.0
     */
    function waboot_do_post_tags() {

        // Return early if theme options are set to hide tags
        if ( ! of_get_option( 'alienship_post_tags', 1 ) )
            return;

        $post_tags = get_the_tags();
        if ( $post_tags ) {

            echo '<span class="tags-links"><i class="glyphicon glyphicon-tags" title="Tags"></i> ';
            $num_tags = count( $post_tags );
            $tag_count = 1;

            foreach( $post_tags as $tag ) {
                $html_before = '<a href="' . get_tag_link($tag->term_id) . '" rel="tag nofollow" class="tag-text">';
                $html_after = '</a>';

                if ( $tag_count < $num_tags )
                    $sep = ', ';
                elseif ( $tag_count == $num_tags )
                    $sep = '';

                echo $html_before . $tag->name . $html_after . $sep;
                $tag_count++;
            }
            echo '</span>';
        }
    }
    add_action( 'waboot_entry_footer', 'waboot_do_post_tags', 9 );
endif;

if ( ! function_exists( 'waboot_do_post_comments_link' ) ):
    /**
     * Display the "Leave a comment" message
     * @since 1.0
     */
    function waboot_do_post_comments_link() {

        // Return early if theme options are set to hide comment link
        if ( ! of_get_option( 'alienship_post_comments_link', 1 ) )
            return;

        if ( comments_open() || ( '0' != get_comments_number() && ! comments_open() ) ) { ?>
            <span class="comments-link">
			<i class="glyphicon glyphicon-comment"></i>
                <?php comments_popup_link( __( ' Leave a comment', 'alienship' ), __( ' 1 Comment', 'alienship' ), __( ' % Comments', 'alienship' ) ); ?>
		</span>
        <?php }
    }
    add_action( 'waboot_entry_footer', 'waboot_do_post_comments_link', 15 );
endif;

/**
 * Print the closing markup for the entry header.
 * @since 1.0
 */
function waboot_entry_footer_markup_close() {
    echo '</footer>';
}
add_action( 'waboot_entry_footer', 'waboot_entry_footer_markup_close', 15 );