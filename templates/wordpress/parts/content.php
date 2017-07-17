<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="post-inner">
        <?php if(has_post_thumbnail()) : ?>
            <div class="post-image">
                <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Link to %s', 'waboot' ), the_title_attribute( 'echo=0' ) ); ?>">
                    <?php echo get_the_post_thumbnail( $post->ID, 'thumbnail', array( 'class' => 'img-responsive', 'title' => "" ) ); ?>
                </a>
            </div>
        <?php endif; ?>
        <div class="post-content">
            <?php do_action( 'waboot/entry/header', 'list' ); ?>
            <?php
                the_excerpt();
                wp_link_pages();
            ?>
            <?php do_action( 'waboot/entry/footer' ); ?>
        </div>
    </div><!-- .entry-content -->
</article>
<!-- #post-<?php the_ID(); ?> -->