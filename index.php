<?php get_header(); ?>
    <div id="main-wrapper">
	    <?php if(function_exists("Waboot")): ?>
        <?php Waboot()->layout->render_zone("aside-primary"); ?>
        <?php Waboot()->layout->render_zone("main"); ?>
        <?php Waboot()->layout->render_zone("aside-secondary"); ?>
		<?php endif; ?>
    </div><!-- #main-wrapper -->
<?php get_footer(); ?>