<?php
/*
 * Waboot View
 */
?>
<main id="main" role="main" class="<?php \Waboot\template_tags\main_classes(); ?>" data-zone="<?php echo $name ?>">
	<div class="content-inner">
		<?php do_action("waboot/main/before"); ?>
		<?php
			/*
			 * @\Waboot\hooks\add_main_content()
			 *
			 * We use a single hook to this zone which acts as router based on page type. The classic wordpress templates can be found into templates/wordpress.
			 */
			Waboot()->layout->do_zone_action($name);
		?>
		<?php do_action("waboot/main/after"); ?>
	</div><!-- .content-inner -->
</main><!-- #main -->