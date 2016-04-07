<?php get_header(); ?>
    <div id="main-wrap">
        <?php Waboot()->layout->render_zone("aside-primary"); ?>
        <?php Waboot()->layout->render_zone("main"); ?>
        <?php Waboot()->layout->render_zone("aside-secondary"); ?>
    </div><!-- #main-wrap -->
<?php get_footer(); ?>