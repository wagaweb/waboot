<?php
/**
 * The template used to load the Top Navbar Menu in header*.php
 *
 * @package Waboot
 * @since 1.0
 */
?>
    <!-- Top Menu -->
	<nav class="navbar top-navigation" role="navigation">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<?php if (of_get_option('waboot_name_in_navbar',1) ) : ?>
				<a class="navbar-brand" href="<?php echo home_url( '/' ); ?>"><?php bloginfo( 'name' ); ?></a>
			<?php endif; ?>
		</div><!-- .navbar-header -->
		<div class="collapse navbar-collapse navbar-ex1-collapse">
            <?php if ( of_get_option('waboot_social_position') === 'topnav-left' ) : ?>
                <div class="pull-left"> <?php include 'social-widget.php'; ?> </div><!-- .pull-left -->
            <?php endif; ?>
            <?php wp_nav_menu( array('theme_location' => 'top','depth' => 0,'container' => false,'menu_class' => 'nav navbar-nav','walker' => new waboot_bootstrap_navwalker(),'fallback_cb' => 'waboot_bootstrap_navwalker::fallback')); ?>
            <?php if ( of_get_option('waboot_social_position') === 'topnav-right' ) : ?>
                <div class="pull-right"> <?php include 'social-widget.php'; ?> </div><!-- .pull-right -->
            <?php endif; ?>
		</div><!-- .collapse navbar-collapse navbar-ex1-collapse -->
	</nav>
    <!-- End Top Menu -->
