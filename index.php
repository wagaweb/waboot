<?php get_header(); ?>
    <?php get_template_part('templates/wrapper', 'start'); ?>
    <?php
    /*
     * We use a single hook to this zone which acts as router based on page type. The classic wordpress templates can be found into templates/wordpress.
     *
     * @\Waboot\inc\core\addMainContent()
     */
    do_action('waboot/layout/content');
    ?>
    <?php get_template_part('templates/wrapper', 'end'); ?>
<?php get_footer(); ?>
