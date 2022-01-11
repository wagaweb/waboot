<?php if ( have_posts() ) : ?>
    <div class="<?php echo apply_filters('waboot/layout/posts_wrapper/class','article__list'); ?>">
        <?php while(have_posts()) :  the_post(); ?>
        <?php get_template_part( '/templates/parts/content', 'search' ); ?>
        <?php endwhile; ?>
    </div>
    <?php \Waboot\inc\renderPostNavigation( 'nav-below' ); ?>
<?php else : ?>
    <?php
    // No results
    get_template_part( '/templates/parts/content', 'none' );
    ?>
<?php endif; ?>
