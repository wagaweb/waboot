<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the id=main div and all content after
 *
 * @package Waboot
 * @since Alien Ship 0.1
 */
?>

	</div><!-- #content -->
	</div><!-- #content-wrapper -->
	
	<?php if ( is_active_sidebar( 'contentbottom' ) ) : ?>
		<div id="contentbottom-wrapper" class="<?php echo of_get_option( 'waboot_bottom_width' ); ?>">
		<div id="contentbottom">
			<?php dynamic_sidebar( 'contentbottom' ); ?>
		</div>
		</div>
	<?php endif; ?>
	
	<div id="footer-wrapper" class="<?php echo of_get_option( 'waboot_footer_width' ); ?>">
	<?php do_action( 'waboot_content_after' );

	// Footer widgets
	waboot_do_sidebar( 'footer' ); ?>
	</div>

</div><!-- #page -->

<?php do_action( 'waboot_footer_before' ); ?>
<footer class="<?php echo of_get_option( 'waboot_footer_width','container' ); ?> site-footer" id="colophon" role="contentinfo">
	<?php do_action( 'waboot_footer_top' ); ?>

	<div class="container">
		<div class="row">

			<div class="bottom-navigation col-sm-8 col-sm-push-4">
				<?php if ( of_get_option('waboot_social_position') === 'footer' ) { ?>
					<div class="pull-right"> <?php get_template_part( 'templates/parts/social-widget'); ?></div>
				<?php } ?>
				
				<?php if ( has_nav_menu( 'bottom' ) ) {
					wp_nav_menu( array(
						'theme_location' => 'bottom',
						'container'      => false,
						'menu_class'     => 'footer-nav mobile'
						)
					);
				} ?>
			</div><!-- .bottom-navigation -->
			<div class="footer-text col-sm-4 col-sm-pull-8">
				<?php if ( of_get_option('waboot_custom_footer_toggle') ) {
					echo '' . of_get_option('waboot_custom_footer_text') . '';
				} else {
					echo '&copy; ' . date('Y') . ' ' . get_bloginfo('name'); } ?>
			</div><!-- .footer-text -->

		</div><!-- .row -->
	</div><!-- .container -->

	<?php do_action( 'waboot_footer_bottom' ); ?>
</footer><!-- #colophon -->

<?php
do_action( 'waboot_footer_after' );

wp_footer();

do_action( 'waboot_footer' ); ?>

</body>
</html>