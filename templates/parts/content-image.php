<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="entry__wrapper">
        <div class="entry__content">
            <?php if(is_singular()) : ?>
                <?php do_action( 'waboot/entry/header' ); ?>
                <?php if(has_post_thumbnail()): ?>
                    <?php echo get_the_post_thumbnail( $post->ID, 'medium', array( 'class' => 'alignnone', 'title' => "" ) ); ?>
                <?php endif; ?>
                <?php the_content(); ?>
            <?php else : ?>
                <?php if(has_post_thumbnail()): ?>
                    <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Link to %s', 'waboot' ), the_title_attribute( 'echo=0' ) ); ?>">
                        <?php echo get_the_post_thumbnail( $post->ID, 'medium', array( 'class' => 'alignnone', 'title' => "" ) ); ?>
                    </a>
                <?php endif; ?>
            <?php endif; ?>

            <?php do_action( 'waboot/entry/footer' ); ?>
        </div>
    </div>
</article>