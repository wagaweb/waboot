<?php
/*
 * Displayed when "banner" sidebar is active.
 * @hooked 'waboot/header' in hooks.php
 */
?>
<div id="banner-wrapper" class="banner-wrapper">
	<div id="banner-inner" class="<?php echo Waboot\functions\get_option('banner_width',WabootLayout()->get_grid_class('container')); ?>" class="banner-inner">
		<?php dynamic_sidebar('banner'); ?>
	</div>
</div>