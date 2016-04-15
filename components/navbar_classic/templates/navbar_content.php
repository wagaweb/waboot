<!-- Main Nav -->
<div class="navbar-header">
	<?php if($show_mobile_nav): ?>
		<button type="button" class="navbar-toggle" data-toggle="offcanvas" data-target=".navbar-mobile-collapse" data-canvas="body">
			<span class="sr-only"><?php _e("Toggle navigation","waboot"); ?></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</button>	
	<?php else : ?>
		<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-main-collapse">
			<span class="sr-only"><?php _e("Toggle navigation","waboot"); ?></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</button>	
	<?php endif; ?>

	<?php if(\Waboot\template_tags\get_desktop_logo() != ""): ?>
		<a class="navbar-brand" href="<?php echo home_url( '/' ); ?>">
			<?php \Waboot\template_tags\desktop_logo(); ?>
		</a>
	<?php else : ?>
		<?php get_bloginfo("title"); ?>
	<?php endif; ?>
</div>

<div class="collapse navbar-collapse navbar-main-collapse">
	<?php if($display_socials): ?>
		<?php the_widget('Waboot\inc\widgets\Social'); ?>
	<?php endif; ?>
	<?php if($display_searchbar): ?>
		<form id="searchform" class="navbar-form navbar-right" role="search" action="<?php echo site_url(); ?>" method="get">
			<div class="form-group">
				<input id="s" name="s" type="text" class="form-control" placeholder="<?php esc_attr_e( 'Search &hellip;', 'waboot' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>">
			</div>
			<button id="searchsubmit" type="submit" name="submit" class="btn btn-default">Submit</button>
		</form>
	<?php endif; ?>
	<?php wp_nav_menu([
		'theme_location' => 'main',
		'depth' => 0,
		'container' => false,
		'menu_class' => apply_filters('waboot/navigation/main/class', 'navbar-nav'),
		'walker' => class_exists('WabootNavMenuWalker') ? new WabootNavMenuWalker() : "", //todo: includere in Waboot on in wbf?
		'fallback_cb' => 'waboot_nav_menu_fallback'
	]); 
	?>
</div>
<!-- End Main Nav -->