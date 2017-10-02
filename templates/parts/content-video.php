<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="entry-content">
        <?php if(is_singular()) : ?>
            <?php do_action( 'waboot/entry/header' ); ?>
            <?php the_content(); ?>
        <?php else : ?>
            <?php do_action( 'waboot/entry/header', 'list' ); ?>
            <?php $first_video = \Waboot\template_tags\get_first_video(); ?>
            <?php if ( $first_video ) : ?>
                <div class="wb-video-container">
                    <?php echo $first_video; ?>
                </div>
            <?php endif; ?>
            <?php the_excerpt(); ?>
        <?php endif; ?>
    </div><!-- .entry-content -->
	<?php do_action( 'waboot/entry/footer' ); ?>
</article>