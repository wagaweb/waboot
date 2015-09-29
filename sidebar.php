<?php
/**
 * The Sidebar containing the main widget areas.
 *
 * @package Waboot
 * @since Waboot 1.0
 */
?>
<?php if ( waboot_get_body_layout() != "full-width" ) : ?>
	<div id="primary" class="<?php echo apply_filters( 'waboot_primary_container_class', 'col-sm-4' ); ?>">
		<div id="sidebar-primary" class="<?php echo apply_filters( 'waboot_sidebar_container_class', 'widget-area' ); ?>" role="complementary">
			<?php do_action("waboot/sidebar/primary/widgets/before"); ?>
			<?php dynamic_sidebar( 'sidebar-1' ); ?>
			<?php do_action("waboot/sidebar/primary/widgets/after"); ?>
		</div><!-- #sidebar -->
	</div><!-- #secondary -->
<?php endif; ?>

<?php if ( waboot_body_layout_has_two_sidebars() ) : ?>
	<div id="secondary" class="<?php echo apply_filters( 'waboot_secondary_container_class', 'col-sm-4' ); ?>">
		<div id="sidebar-secondary" class="<?php echo apply_filters( 'waboot_sidebar_container_class', 'widget-area' ); ?>" role="complementary">
			<?php do_action("waboot/sidebar/secondary/widgets/before"); ?>
			<?php dynamic_sidebar( 'sidebar-2' ); ?>
			<?php do_action("waboot/sidebar/secondary/widgets/after"); ?>
		</div><!-- #sidebar -->
	</div><!-- #secondary -->
<?php endif; ?>