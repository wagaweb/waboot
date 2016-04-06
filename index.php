<?php
get_header();
?>
    <div id="main-wrap">
        <?php Waboot()->layout->aside("primary"); ?>
        <?php Waboot()->layout->main(); ?>
        <?php Waboot()->layout->aside("secondary"); ?>
    </div><!-- #main-wrap -->
<?php
get_footer();