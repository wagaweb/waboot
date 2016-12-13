<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="entry-content">
        <?php if(is_singular()) : ?>
            <?php do_action( 'waboot/entry/header' ); ?>
            <?php \Waboot\template_tags\display_post_gallery(); ?>
            <?php the_content(); ?>
        <?php else : ?>
            <?php do_action( 'waboot/entry/header', 'list' ); ?>
            <?php \Waboot\template_tags\display_post_gallery(); ?>
            <?php the_excerpt(); ?>
        <?php endif; ?>
    </div><!-- .entry-content -->
    <?php do_action( 'waboot/entry/footer' ); ?>
</article>