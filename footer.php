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

</div><!-- #page -->
<footer class="site-footer" id="colophon" role="contentinfo">
	<div class="<?php echo of_get_option( 'waboot_footer_width','container' ); ?>">
		<div class="row">

			<div class="bottom-navigation col-sm-6 col-sm-push-6">
				<?php if ( of_get_option('waboot_social_position') === 'footer' ) { ?>
					<?php get_template_part( 'templates/parts/social-widget'); ?>
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
			<div class="footer-text col-sm-6 col-sm-pull-6">
				<?php if ( of_get_option('waboot_custom_footer_toggle') ) {
					echo '' . of_get_option('waboot_custom_footer_text') . '';
				} else {
					echo '&copy; ' . date('Y') . ' ' . get_bloginfo('name'); } ?>
			</div><!-- .footer-text -->

		</div><!-- .row -->
	</div><!-- .container -->
</footer><!-- #colophon -->
<?php
    wp_footer();
    do_action( 'waboot_footer' );
?>
</body>
</html>