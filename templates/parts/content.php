<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="article__inner">
        <?php if(has_post_thumbnail()) : ?>
        <figure class="article__image">
            <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Link to %s', LANG_TEXTDOMAIN ), the_title_attribute( 'echo=0' ) ); ?>">
                <?php echo get_the_post_thumbnail( $post->ID, 'thumbnail') ?>
            </a>
        </figure>
        <?php endif; ?>
        <div class="article__content">
            <h2><?php the_title(); ?></h2>
            <p>
                <?php \Waboot\inc\trimmedExcerpt(20, '...'); ?>
                <a class="more__link" href="<?php the_permalink() ?>">
                    <?php _e('Continue reading', LANG_TEXTDOMAIN) ?>
                </a>
            </p>
            <?php do_action( 'waboot/article/list/footer' ); ?>
        </div>
    </div>
</article>
