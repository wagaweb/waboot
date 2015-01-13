<?php
/**
 * The Sidebar containing the main widget areas.
 *
 * @package Waboot
 * @since Waboot 1.0
 */
?>
<?php //if ( current_theme_supports( 'theme-layouts' ) && !is_admin() && 'layout-1c' !== theme_layouts_get_layout() || !current_theme_supports( 'theme-layouts' ) ) : ?>
<?php if ( waboot_get_body_layout() != "full-width" ) : ?>
	<div id="primary" class="<?php echo apply_filters( 'waboot_primary_container_class', 'col-sm-4' ); ?>">
		<div id="sidebar-primary" class="<?php echo apply_filters( 'waboot_sidebar_container_class', 'widget-area' ); ?>" role="complementary">
			<?php dynamic_sidebar( 'sidebar-1' ); ?>
		</div><!-- #sidebar -->
	</div><!-- #secondary -->
<?php endif; ?>

<?php if ( waboot_body_layout_has_two_sidebars() ) : ?>
	<div id="secondary" class="<?php echo apply_filters( 'waboot_secondary_container_class', 'col-sm-4' ); ?>">
		<div id="sidebar-secondary" class="<?php echo apply_filters( 'waboot_sidebar_container_class', 'widget-area' ); ?>" role="complementary">
			<?php dynamic_sidebar( 'sidebar-2' ); ?>
		</div><!-- #sidebar -->
	</div><!-- #secondary -->
<?php endif; ?>