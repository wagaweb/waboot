<div class="collapse navbar-collapse navbar-mobile-collapse offcanvas">
    
    <?php if( ($logo_offcanvas) && ( \Waboot\functions\get_option('offcanvas_logo') != '' ) ) : ?>
        <div class="logo-offcanvas">
            <img src="<?php echo \Waboot\functions\get_option('offcanvas_logo', ""); ?>">
        </div>
    <?php endif; ?>

    <?php wp_nav_menu( array(
        'theme_location' => 'main',
        'depth'          => 0,
        'container'      => false,
        'menu_class'     => 'nav navbar-nav',
        'walker'	     => new WabootNavMenuWalker(),
        'fallback_cb' => 'waboot_nav_menu_fallback'
    )); ?>

    <?php wp_nav_menu( array(
        'theme_location' => 'top',
        'depth' => 0,
        'container' => false,
        'menu_class' => 'nav navbar-nav',
        'walker' => new WabootNavMenuWalker(),
        'fallback_cb' => 'waboot_nav_menu_fallback'
    )); ?>

    <?php if($display_searchbar): ?>
        <form id="searchform" class="navbar-form" role="search" action="<?php echo site_url(); ?>" method="get">
            <div class="form-group">
                <input id="s" name="s" type="text" class="form-control" placeholder="<?php esc_attr_e( 'Search &hellip;', 'waboot' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>">
            </div>
            <button id="searchsubmit" type="submit" name="submit" class="btn btn-default">Submit</button>
        </form>
    <?php endif; ?>

</div>