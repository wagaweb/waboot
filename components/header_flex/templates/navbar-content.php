<nav class="main-navigation nav-<?php echo $nav_align; ?>" role="navigation">
    <?php wp_nav_menu([
        'theme_location' => 'main',
        'depth' => 0,
        'container' => false,
        'menu_class' => apply_filters('waboot/navigation/main/class', 'nav navbar-nav')
    ]);
    ?>
    <?php if($display_searchbar): ?>
        <form id="searchform" class="navbar-form" role="search" action="<?php echo site_url(); ?>" method="get">
            <input id="s" name="s" type="text" placeholder="<?php esc_attr_e( 'Search &hellip;', 'waboot' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>">
            <button id="searchsubmit" type="submit" name="submit">Submit</button>
        </form>
    <?php endif; ?>
</nav>