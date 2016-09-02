<?php get_header(); ?>
	<?php get_template_part("templates/wrapper","start"); ?>
	<?php
	/*
	 * content zone
	 */
	try{
		/*
		 * @\Waboot\hooks\add_main_content()
		 *
		 * We use a single hook to this zone which acts as router based on page type. The classic wordpress templates can be found into templates/wordpress.
		 * The template to this zone is located in templates/content.php
		 */
		Waboot()->layout->render_zone("content");
	}catch(Exception $e){
		$e = new \WBF\components\mvc\HTMLView("templates/view-parts/content-errors.php");
		$e->clean()->display(['Error' => $e,'message' => $e->getMessage()]);
	}
	?>
	<?php get_template_part("templates/wrapper","end"); ?>
<?php get_footer(); ?>