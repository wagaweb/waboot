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
		<div class="collapse navbar-collapse navbar-ex1-collapse">
            <?php wp_nav_menu( array('theme_location' => 'top','depth' => 0,'container' => false,'menu_class' => 'nav navbar-nav','walker' => new WabootNavMenuWalker())); ?>
		</div><!-- .collapse navbar-collapse navbar-ex1-collapse -->
	</nav>
    <!-- End Top Menu -->
