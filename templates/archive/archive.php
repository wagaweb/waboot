<?php use function Waboot\inc\Waboot;

if(have_posts()) : ?>
    <div class="<?php echo apply_filters('waboot/layout/posts_wrapper/class','entry__list'); ?>">
        <?php while(have_posts()): ?>
            <?php the_post(); ?>
            <?php get_template_part( '/templates/parts/content', get_post_format() ); ?>
        <?php endwhile; ?>
    </div>
    <?php \Waboot\inc\renderPostNavigation('nav-below'); ?>
<?php endif; ?>
