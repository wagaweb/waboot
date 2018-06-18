<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="entry__wrapper">
        <div class="entry__content">
            <?php if(is_singular()) : ?>
                <?php do_action( 'waboot/entry/header' ); ?>
                <blockquote>
                    <?php the_content(); ?>
                </blockquote>
            <?php else : ?>
                <?php do_action( 'waboot/entry/header', 'list' ); ?>
                <blockquote>
                    <p><?php \Waboot\template_tags\the_trimmed_excerpt(20, '...'); ?> <a class="more__link" href="<?php the_permalink() ?>"><?php _e('Continue reading', 'waboot') ?></a></p>
                </blockquote>
            <?php endif; ?>
            <?php do_action( 'waboot/entry/footer' ); ?>
        </div>
    </div>

</article>