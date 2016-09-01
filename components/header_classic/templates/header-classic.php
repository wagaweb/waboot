<div id="header-classic-wrapper" class="header-classic-wrapper">
	<div class="header-classic-inner <?php echo $header_width; ?>">
		<!-- Header Classic -->
		<div class="row header-blocks hidden-sm hidden-xs">
			<div id="header-left" class="col-md-3 vcenter">
				<?php if($display_socials && $social_position == "header-left"): ?>
					<?php the_widget('Waboot\inc\widgets\Social'); ?>
				<?php endif; ?>
				<?php dynamic_sidebar( 'header-left' ); ?>
			</div><!--
	        --><div id="logo" class="col-md-6 vcenter">
				<?php if ( \Waboot\template_tags\get_desktop_logo() != "" ) : ?>
					<?php \Waboot\template_tags\desktop_logo(true); ?>
				<?php else : ?>
					<?php
					\Waboot\template_tags\site_title();
					\Waboot\template_tags\site_description();
					?>
				<?php endif; ?>
			</div><!--
	        --><div id="header-right" class="col-md-3 vcenter">
				<?php if($display_socials && $social_position == "header-right"): ?>
					<?php the_widget('Waboot\inc\widgets\Social'); ?>
				<?php endif; ?>
				<?php dynamic_sidebar( 'header-right' ); ?>
			</div>
		</div>
		<!-- End Header Classic -->
	</div>
</div><!-- #header-wrapper -->