<?php
/**
 * The Sidebar containing the main widget areas.
 *
 * @package Waboot
 * @since Waboot 1.0
 */
?>
<?php //if ( current_theme_supports( 'theme-layouts' ) && !is_admin() && 'layout-1c' !== theme_layouts_get_layout() || !current_theme_supports( 'theme-layouts' ) ) : ?>
<?php if ( get_behavior( 'layout' ) != "full-width" ) : ?>

	<?php do_action( 'waboot_secondary_before' ); ?>
	<div id="secondary" class="<?php echo apply_filters( 'waboot_secondary_container_class', 'col-sm-4' ); ?>">

		<?php do_action( 'waboot_sidebar_before' ); ?>
		<div id="sidebar" class="<?php echo apply_filters( 'waboot_sidebar_container_class', 'widget-area' ); ?>" role="complementary">
			<?php
			do_action( 'waboot_sidebar_top' );
			dynamic_sidebar( 'sidebar-1' );
			do_action( 'waboot_sidebar_bottom' );
			?>
		</div><!-- #sidebar -->
	<?php do_action( 'waboot_sidebar_after' ); ?>

	</div><!-- #secondary -->
	<?php do_action( 'waboot_secondary_after' );

endif; ?>