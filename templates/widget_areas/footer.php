<?php
/*
 * Waboot View
 */
?>
<div id="footer-wrapper">
	<div id="footer-inner" class="<?php echo Waboot\functions\get_option('waboot_footer_width', 'container'); ?>">
		<?php
		// Footer widgets
		\Waboot\functions\print_widgets_in_area('footer');
		?>
	</div>
</div>