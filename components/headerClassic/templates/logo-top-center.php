<div id="header-wrapper" class="header-wrapper classic">
	<div id="header-inner" class="<?php echo $header_width; ?>">
		<header id="masthead" class="site-header" role="banner">
			<!-- Header Classic -->
			<div class="row header-blocks hidden-sm hidden-xs">
				<div id="header-left" class="col-md-3 vcenter">
					<?php if($display_socials && $social_position == "header-left"): ?>
						<?php the_widget('Waboot\inc\widgets\Social'); ?>
					<?php endif; ?>
					<?php dynamic_sidebar( 'header-left' ); ?>
				</div>
				<div id="header-right" class="col-md-3 vcenter">
					<?php if($display_socials && $social_position == "header-right"): ?>
						<?php the_widget('Waboot\inc\widgets\Social'); ?>
					<?php endif; ?>
					<?php dynamic_sidebar( 'header-right' ); ?>
				</div>
			</div>
			<!-- End Header Classic -->
		</header>
	</div>
</div><!-- #header-wrapper -->