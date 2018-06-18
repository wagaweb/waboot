<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="entry__wrapper">

        <?php if(has_post_thumbnail()) : ?>
            <div class="entry__image">
                <?php
                $thumb_preset = apply_filters('waboot/layout/entry/thumbnail/preset','thumbnail');
                $thumb_classes = apply_filters('waboot/layout/entry/thumbnail/class','img-responsive');
                ?>
                <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Link to %s', 'waboot' ), the_title_attribute( 'echo=0' ) ); ?>">
                    <?php echo get_the_post_thumbnail( $post->ID, $thumb_preset, array( 'class' => $thumb_classes, 'title' => "" ) ); ?>
                </a>
            </div>
        <?php endif; ?>

        <div class="entry__content">

            <?php do_action( 'waboot/entry/header', 'list' ); ?>

            <p><?php \Waboot\template_tags\the_trimmed_excerpt(20, '...'); ?> <a class="more__link" href="<?php the_permalink() ?>"><?php _e('Continue reading', 'waboot') ?></a></p>


            <?php wp_link_pages(); ?>

            <?php do_action( 'waboot/entry/footer' ); ?>

        </div>

    </div>
</article>