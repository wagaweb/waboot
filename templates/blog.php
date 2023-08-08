<?php if(have_posts()): ?>
    <div class="<?php echo apply_filters('waboot/layout/posts_wrapper/class','article__list'); ?>">
        <?php while(have_posts()) :  the_post(); ?>
            <?php get_template_part( '/templates/parts/content', get_post_format() ); ?>
        <?php endwhile; ?>
    </div>
    <?php \Waboot\inc\renderPostNavigation( 'nav-below' ); // display content nav below posts if needed ?>
<?php else: ?>
    <?php get_template_part('/templates/parts/content', 'none'); // No results ?>
<?php endif; //have_posts ?>
