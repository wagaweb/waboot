<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="entry-content">
        <?php if(is_singular()) : ?>
            <?php do_action( 'waboot/entry/header' ); ?>
            <?php echo do_shortcode( '[audio]' ); ?>
            <?php the_content(); ?>
        <?php else : ?>
            <?php do_action( 'waboot/entry/header', 'list' ); ?>
            <?php echo do_shortcode( '[audio]' ); ?>
            <?php the_excerpt(); ?>
        <?php endif; ?>
    </div><!-- .entry-content -->
    <?php do_action( 'waboot/entry/footer' ); ?>
</article>
