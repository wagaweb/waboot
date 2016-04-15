<div id="topnav-wrapper">
	<div id="topnav-inner" class="<?php echo Waboot\functions\get_option( 'waboot_topnav_width','container-fluid' ); ?> ">
		<?php if($display_socials): ?>
			<div class="<?php echo $social_position_class ?>">
				<?php the_widget('Waboot\inc\widgets\Social'); ?>
			</div>
		<?php endif; ?>
		<?php if($display_topnav): ?>
			<div class="<?php echo $topnavmenu_position_class; ?>">
				<?php get_template_part("components/topNavWrappers/templates/parts/nav","top"); ?>
			</div>
		<?php endif; ?>
		<?php dynamic_sidebar( 'topbar' ); ?>
	</div>
</div>