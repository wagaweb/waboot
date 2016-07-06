<div id="footer-wrapper">
	<div id="footer-inner" class="<?php echo Waboot\functions\get_option('waboot_footer_width', 'container'); ?>">
		<?php
		// Footer widgets
		if(\Waboot\functions\count_widgets_in_area("footer") == 0){
			\Waboot\functions\print_widgets_in_area('footer');
		}
		?>
		<div data-cookieonly  class="site-footer closure" id="colophon" role="contentinfo">
			<div id="closure-inner" class="<?php echo $closure_width; ?>">

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
	</div>
</div>