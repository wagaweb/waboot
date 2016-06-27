<div id="closure-inner" class="<?php echo of_get_option( 'waboot_closure_width','container' ); ?>">

	<div class="footer-text">
		<?php
		if($footer_toggle){
			echo '' . $custom_footer_text . '';
		}else{
			echo '&copy; ' . date('Y') . ' ' . get_bloginfo('name');
		}
		?>
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