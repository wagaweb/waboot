<?php
/*
 * Waboot View
 */
?>
<main id="main" role="main" class="<?php \Waboot\template_tags\main_classes(); ?>">
	<div class="content-inner">
		<?php do_action("waboot/main/before"); ?>
		<?php Waboot()->layout->do_zone_action($name); ?>
		<?php do_action("waboot/main/after"); ?>
	</div><!-- .content-inner -->
</main><!-- #main -->