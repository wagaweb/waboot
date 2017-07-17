<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="entry-content row">
		<?php /* -------- article with thumbnail -------- */ ?>
		<?php if(has_post_thumbnail()) : ?>
            <div class="col-md-8 pull-right-md">
				<?php do_action( 'waboot/entry/header', 'list' ); ?>
            </div>
            <div class="entry-image col-sm-4 ">
                <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Link to %s', 'waboot' ), the_title_attribute( 'echo=0' ) ); ?>">
					<?php echo get_the_post_thumbnail( $post->ID, 'thumbnail', array( 'class' => 'img-responsive', 'title' => "" ) ); ?>
                </a>
            </div>
            <div class="col-sm-8 pull-right-sm">
				<?php
				the_excerpt();
				wp_link_pages();
				?>
				<?php do_action( 'waboot/entry/footer' ); ?>
            </div>
		<?php else : ?>
			<?php /* -------- article without thumbnail -------- */ ?>
            <div class="col-sm-12">
				<?php do_action( 'waboot/entry/header', 'list' ); ?>
				<?php
				the_excerpt();
				wp_link_pages();
				?>
				<?php do_action( 'waboot/entry/footer' ); ?>
            </div>
		<?php endif; ?>
    </div><!-- .entry-content -->
</article>
<!-- #post-<?php the_ID(); ?> -->