<div id="footer-classic-wrapper">
	<div class="footer-classic-inner <?php echo Waboot\functions\get_option('footer_classic_width', 'container'); ?>">
		<?php
		// Footer widgets
		\Waboot\functions\print_widgets_in_area('footer-classic');
		?>
		<?php do_action('waboot/component/footer-classic/after_widgets')?>
	</div>
</div>
<div data-cookieonly class="site-footer closure" id="colophon" role="contentinfo">
	<div class="closure-inner <?php echo $closure_width; ?>">

		<div class="footer-text">
			<?php echo $footer_text; ?>
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

		<?php if ( $social_position == 'footer' && $display_socials ) : ?>
			<?php get_template_part( 'templates/parts/social-widget'); ?>
		<?php endif; ?>

	</div><!-- #closure-inner -->
</div><!-- .closure -->