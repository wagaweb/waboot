<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="entry__wrapper">
        <div class="entry__content">
            <?php if(is_singular()) : ?>
                <?php do_action( 'waboot/entry/header' ); ?>
                <?php the_content(); ?>
            <?php else : ?>
                <header class="entry__title">
                    <h2>
                        <a title="<?php printf( esc_attr__( 'Link to %s', 'waboot' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark" href="<?php echo Waboot\template_tags\get_filtered_link_post_content( 'link' ); ?>" target="_blank">
                            <?php the_title(); ?> &rarr;
                        </a>
                    </h2>
                </header>
                <?php echo Waboot\template_tags\get_filtered_link_post_content( 'post_content' ); ?>
            <?php endif; ?>

            <?php do_action( 'waboot/entry/footer' ); ?>
        </div>
    </div>
</article>