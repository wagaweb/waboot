<?php $vars = \Waboot\functions\get_archives_template_vars(); ?>

<?php
$o = get_queried_object();
$tpl = "";
if($o instanceof WP_Term){
    $tpl = "taxonomy-".$o->taxonomy;
}elseif($o instanceof WP_Post_Type){
    $tpl = "archive-".$o->name;
}
?>

<?php if(!empty($tpl) && locate_template("templates/wordpress/archive/".$tpl.".php", false, false) != '') : ?>
    <?php get_template_part('templates/wordpress/archive/'.$tpl); ?>
<?php else: ?>
    <?php if(have_posts()) : ?>
        <?php if($vars['display_nav_above']) \Waboot\template_tags\post_navigation('nav-above'); ?>
        <div class="<?php echo $vars['blog_class']; ?>">
            <?php //waboot_archive_sticky_posts($blog_style); // Display the sticky posts first... ?>
            <?php while(have_posts()): ?>
                <?php the_post(); ?>
                <?php \Waboot\functions\get_template_part( '/templates/wordpress/parts/content', get_post_format() ); ?>
            <?php endwhile; ?>
        </div>
        <?php if($vars['display_nav_below']) \Waboot\template_tags\post_navigation('nav-below'); ?>
    <?php endif; ?>
<?php endif; ?>