<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="entry-content">

		<?php if(has_post_thumbnail()) : ?>
            <?php get_template_part('/templates/view-parts/entry','thumbnail'); ?>
        <?php endif; ?>

        <div class="entry-text">

            <?php do_action( 'waboot/entry/header', 'list' ); ?>

            <?php
            the_excerpt();
            wp_link_pages();
            ?>

            <?php do_action( 'waboot/entry/footer' ); ?>
        </div>

    </div>
</article>
