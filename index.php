<?php get_header(apply_filters("waboot/get_header",'')); ?>
	<?php get_template_part("templates/wrapper","start"); ?>
	<?php
	/*
	 * content zone
	 */
	try{
		/*
		 * We use a single hook to this zone which acts as router based on page type. The classic wordpress templates can be found into templates/wordpress.
		 *
		 * @\Waboot\hooks\add_main_content()
		 */
		\Waboot\template_tags\render_zone("content");
	}catch(Exception $error){
		$e = new \WBF\components\mvc\HTMLView("templates/view-parts/content-errors.php");
		$e->clean()->display(['Error' => $error,'message' => $error->getMessage()]);
	}
	?>
	<?php get_template_part("templates/wrapper","end"); ?>
<?php get_footer(apply_filters("waboot/get_footer",'')); ?>