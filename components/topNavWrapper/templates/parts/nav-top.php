 <!-- Top Menu -->
<nav class="navbar top-navigation" role="navigation">
	<div class="collapse navbar-collapse navbar-top-collapse">
        <?php wp_nav_menu( array(
            'theme_location' => 'top',
            'depth' => 0,
            'container' => false,
            'menu_class' => 'nav navbar-nav',
            'walker' => new WabootNavMenuWalker(),
            'fallback_cb' => 'waboot_nav_menu_fallback'
        )); ?>
	</div><!-- .collapse navbar-collapse navbar-ex1-collapse -->
</nav>
<!-- End Top Menu -->
