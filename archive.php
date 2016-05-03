<?php get_header(); ?>
	<div id="main-wrap">
		<?php Waboot()->layout->render_zone("aside-primary"); ?>
		<main>
			<?php get_template_part("templates/wordpress/archive","content"); //method 1 ?>
			<?php Waboot()->layout->render_wp_template_content("archive.php"); //method 2 ?>
			<?php Waboot\functions\render_archives("templates/wordpress/archive.php"); //method 3 ?>
		</main>
		<?php Waboot()->layout->render_zone("aside-secondary"); ?>
	</div><!-- #main-wrap -->
<?php get_footer(); ?>