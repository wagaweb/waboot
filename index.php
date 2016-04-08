<?php get_header(); ?>
    <div id="main-wrap">
	    <?php if(function_exists("Waboot")): ?>
        <?php Waboot()->layout->render_zone("aside-primary"); ?>
        <?php Waboot()->layout->render_zone("main"); ?>
        <?php Waboot()->layout->render_zone("aside-secondary"); ?>
		<?php endif; ?>
    </div><!-- #main-wrap -->
<?php get_footer(); ?>