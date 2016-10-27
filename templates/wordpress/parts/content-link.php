<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="entry-content">
        <?php if(is_singular()) : ?>
            <?php do_action( 'waboot/entry/header' ); ?>
            <?php the_content(); ?>
        <?php else : ?>
            <h2 class="post-title">
                <a class="entry-title" title="<?php printf( esc_attr__( 'Link to %s', 'waboot' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark" href="<?php echo Waboot\template_tags\get_filtered_link_post_content( 'link' ); ?>" target="_blank">
                    <?php the_title(); ?> &rarr;
                </a>
            </h2>
            <?php echo Waboot\template_tags\get_filtered_link_post_content( 'post_content' ); ?>
        <?php endif; ?>
    </div><!-- .entry-content -->
    <?php do_action( 'waboot/entry/footer' ); ?>
</article>