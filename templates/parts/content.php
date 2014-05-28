<?php
/**
 * @package Waboot
 * @since Waboot 1.0
 */

do_action( 'waboot_post_before' ); ?>
<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php
	do_action( 'waboot_post_top' );
	do_action( 'waboot_entry_header' );
	do_action( 'waboot_entry_content_before' );
	?>
	<div class="entry-content">
		<?php
			if(has_post_thumbnail()) : ?>
				<a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Link to %s', 'waboot' ), the_title_attribute( 'echo=0' ) ); ?>">
                    <?php
                        if(is_singular()){
                            echo get_the_post_thumbnail( $post->ID, 'medium', array( 'class' => 'alignright', 'title' => "" ) );
                        }else{
                            echo get_the_post_thumbnail( $post->ID, 'thumbnail', array( 'class' => 'alignleft', 'title' => "" ) );
                        }
                    ?>
				</a>
			<?php endif;

            if(is_singular()){
                the_content();
            }else{
                the_excerpt();
            }

		    wp_link_pages();
        ?>
	</div><!-- .entry-content -->
	<?php
	do_action( 'waboot_entry_content_after' );
    do_action( 'waboot_entry_footer' );
	do_action( 'waboot_post_bottom' );
	?>
</article><!-- #post-<?php the_ID(); ?> -->
<?php do_action( 'waboot_post_after' ); ?>