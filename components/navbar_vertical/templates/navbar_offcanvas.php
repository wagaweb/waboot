<div class="collapse navbar-collapse navbar-mobile-collapse offcanvas">

    <?php wp_nav_menu( array(
        'theme_location' => 'main',
        'depth'          => 0,
        'container'      => false,
        'menu_class'     => 'nav navbar-nav',
        'walker'         => new \WBF\components\navwalker\Bootstrap_NavWalker(),
        'fallback_cb' => '\WBF\components\navwalker\Bootstrap_NavWalker::fallback'
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