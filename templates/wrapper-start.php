<?php
$main_wrapper_vars = \Waboot\functions\get_main_wrapper_template_vars();
?>
<div id="main-wrapper" class="<?php echo $main_wrapper_vars['classes']; ?>">
	<div class="main-inner">
		<?php
		/*
		 * main-top zone
		 */
        WabootLayout()->render_zone("main-top");
		?>
		<?php
        /*
         * Here we print the singular title when "title_position" option is on "top".
         * @see: posts_and_pages.php
         */
        do_action("waboot/site-main/before");
        ?>
		<div class="<?php \Waboot\template_tags\container_classes(); ?>">
			<div class="<?php echo WabootLayout()->get_grid_class('row'); ?>">
				<main id="main" role="main" class="<?php \Waboot\template_tags\main_classes(); ?>" data-zone="<?php echo $name ?>">
						<div class="content-inner">
							<?php do_action("waboot/main/before"); ?>