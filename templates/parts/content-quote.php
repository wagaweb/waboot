<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="entry-content">
        <?php if(is_singular()) : ?>
            <?php do_action( 'waboot/entry/header' ); ?>
            <blockquote>
                <?php the_content(); ?>
            </blockquote>
        <?php else : ?>
            <?php do_action( 'waboot/entry/header', 'list' ); ?>
            <blockquote>
                <?php the_excerpt(); ?>
            </blockquote>
        <?php endif; ?>
    </div><!-- .entry-content -->
    <?php do_action( 'waboot/entry/footer' ); ?>
</article>