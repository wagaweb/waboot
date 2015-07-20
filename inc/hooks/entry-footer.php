<?php

if(!function_exists( 'waboot_entry_footer_open_tag')):
	function waboot_entry_footer_open_tag(){
		echo '<footer class="entry-footer">';
	}
	add_action("waboot_entry_footer","waboot_entry_footer_open_tag");
endif;

if(!function_exists( 'waboot_entry_footer_close_tag')):
	function waboot_entry_footer_close_tag(){
		echo '</footer>';
	}
	add_action("waboot_entry_footer","waboot_entry_footer_close_tag",9999);
endif;

if(!function_exists( 'waboot_do_posted_on')):
	/**
	 * Prints HTML with date posted information for the current post.
	 *
	 * @param bool|false $relative_time can be used to print the link text in the relative format
	 */
	function waboot_do_posted_on($relative_time = false) {
        // Return early if theme options are set to hide date
        if ( ! of_get_option( 'waboot_published_date', 1 ) )
            return;

        printf( __( '<span class="published-date"><a href="%1$s" title="%2$s"><time class="entry-date" datetime="%3$s">%4$s</time></a></span>', 'waboot' ),
            esc_url( get_permalink() ),
            esc_attr( get_the_time() ),
            esc_attr( get_the_date( 'c' ) ),
            !$relative_time ? esc_html( get_the_date() ) : sprintf( _x( '%s ago', 'Relative date output for entry footer' ,'waboot' ), human_time_diff( get_the_date( 'U' ), current_time( 'timestamp' ) ) )
        );
    }
	add_action("waboot_entry_footer","waboot_do_posted_on",10);
endif;

if(!function_exists( 'waboot_do_post_author')):
    /**
     * Prints HTML with meta information for the current post's author.
     * @since 0.1.0
     */
    function waboot_do_post_author() {
        // Return early if theme options are set to hide author
        if ( ! of_get_option('waboot_post_author', 1 ) )
            return;

        printf( __( '<span class="byline"><span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></span></span>', 'waboot' ),
            esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
            esc_attr( sprintf( __( 'View all posts by %s', 'waboot' ), get_the_author() ) ),
            esc_html( get_the_author() )
        );
    }
	add_action("waboot_entry_footer","waboot_do_post_author",11);
endif;

if(!function_exists( 'waboot_do_post_categories')):
    /**
     * Display the list of categories on a post
     */
    function waboot_do_post_categories() {
        // Return early if theme options are set to hide categories
        if ( ! of_get_option( 'waboot_post_categories', 1 ) )
            return;

	    echo wbft_get_the_terms_list_hierarchical( get_the_ID(), 'category', '<span class="cat-links">', ', ', '</span>' );
    }
	add_action("waboot_entry_footer","waboot_do_post_categories",12);
endif;

if(!function_exists( 'waboot_do_post_tags')):
    /**
     * Customize the list of tags displayed on index and on a post
     * @since 0.1.0
     */
    function waboot_do_post_tags() {
        // Return early if theme options are set to hide tags
        if ( ! of_get_option( 'waboot_post_tags', 1 ) )
            return;

        $post_tags = get_the_tags();
        if($post_tags){
            echo '<span class="tags-links">';
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
	add_action("waboot_entry_footer","waboot_do_post_tags",13);
endif;

if(!function_exists('waboot_do_post_comments_link')):
    /**
     * Display the "Leave a comment" message
     * @since 0.1.0
     */
    function waboot_do_post_comments_link() {
        // Return early if theme options are set to hide comment link
        if ( ! of_get_option( 'waboot_post_comments_link', 1 ) )
            return;

        if ( comments_open() || ( '0' != get_comments_number() && ! comments_open() ) ) : ?>
        <span class="comments-link">
			<i class="glyphicon glyphicon-comment"></i>
            <?php comments_popup_link( __( ' Leave a comment', 'waboot' ), __( ' 1 Comment', 'waboot' ), __( ' % Comments', 'waboot' ) ); ?>
		</span>
        <?php endif;
    }
	add_action("waboot_entry_footer","waboot_do_post_comments_link",14);
endif;