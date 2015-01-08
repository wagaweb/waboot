<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the id=main div and all content after
 *
 * @package Waboot
 * @since Waboot 1.0
 */
?>

	</div><!-- #content -->
	</div><!-- #content-inner -->
    </div><!-- #content-wrapper -->
	
	<?php if ( is_active_sidebar( 'contentbottom' ) ) : ?>
        <div id="contentbottom-wrapper">
            <div id="contentbottom-inner" class="<?php echo of_get_option( 'waboot_bottom_width','container' ); ?>">
			    <?php dynamic_sidebar( 'contentbottom' ); ?>
		    </div>
		</div>
	<?php endif; ?>

	<?php if (waboot_has_sidebar('footer')) : ?>
		<div id="footer-wrapper">
			<div id="footer-inner" class="<?php echo of_get_option('waboot_footer_width', 'container'); ?>">
				<?php
				// Footer widgets
				waboot_do_sidebar('footer');
				?>
			</div>
		</div>
	<?php endif; ?>

	<footer class="site-footer closure" id="colophon" role="contentinfo">
		<div id="closure-inner" class="<?php echo of_get_option( 'waboot_closure_width','container' ); ?>">

			<div class="footer-text">
				<?php if ( of_get_option('waboot_custom_footer_toggle') ) {
					echo '' . of_get_option('waboot_custom_footer_text') . '';
				} else {
					echo '&copy; ' . date('Y') . ' ' . get_bloginfo('name'); } ?>
			</div>

			<div class="bottom-navigation">
				<?php if ( has_nav_menu( 'bottom' ) ) {
					wp_nav_menu( array(
							'theme_location' => 'bottom',
							'container'      => false,
							'menu_class'     => 'footer-nav mobile'
						)
					);
				} ?>
			</div>

			<?php if ( of_get_option('waboot_social_position') === 'footer' ) : ?>
				<?php get_template_part( 'templates/parts/social-widget'); ?>
			<?php endif; ?>

		</div><!-- #closure-inner -->
	</footer><!-- .closure -->
	<?php
		wp_footer();
		do_action( 'waboot_footer' );
	?>

</div><!-- #page -->
</body>
</html>