<?php
/**
 * The template used to load the Main Menu in header*.php
 *
 * @package Waboot
 * @since Alien Ship 0.70
 */
?>
<!-- Main menu -->

<nav id="navbar-2" class="<?php echo apply_filters( 'alienship_main_navbar_class' , 'navbar navbar-default main-navigation' ); ?>" role="navigation">
    <div class="row">

        <div id="header-right" class="col-md-3 col-xs-6">
            <?php if ( of_get_option('waboot_social_position') === 'header-right' ) { include 'social-widget.php'; } ?>
            <?php dynamic_sidebar( 'header-right' ); ?>
        </div>

        <div id="header-left" class="col-md-3 col-xs-6">
            <?php if ( of_get_option('waboot_social_position') === 'header-left' ) { include 'social-widget.php'; } ?>
            <?php dynamic_sidebar( 'header-left' ); ?>
        </div>

        <div id="logo" class="col-md-6 col-xs-12">
            <?php if ( of_get_option( 'waboot_logo_in_navbar' ) ) : ?>
                <a href="<?php echo home_url( '/' ); ?>"><img src="<?php echo of_get_option( 'waboot_logo_in_navbar' ); ?>"> </a>
            <?php else : ?>
                <?php
                do_action( 'waboot_site_title' );
                // do_action( 'waboot_site_description' );
                ?>
            <?php endif; ?>

            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex2-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <?php if (of_get_option('waboot_name_in_navbar',1) ) : ?>
                    <a class="navbar-brand" href="<?php echo home_url( '/' ); ?>"><?php bloginfo( 'name' ); ?></a>
                <?php endif; ?>
            </div>

        </div>

        <div class="collapse navbar-collapse navbar-ex2-collapse">
            <?php wp_nav_menu( array(
                    'theme_location' => 'main',
                    'depth'          => 2,
                    'container'      => false,
                    'menu_class'     => 'nav navbar-nav',
                    'walker'         => new waboot_bootstrap_navwalker(),
                    'fallback_cb'    => 'waboot_bootstrap_navwalker::fallback'
                )
            );

            if ( of_get_option( 'waboot_search_bar', '1' ) ) : ?>
                <form id="searchform" class="navbar-form navbar-right" role="search" action="<?php echo site_url(); ?>" method="get">
                    <div class="form-group">
                        <input id="s" name="s" type="text" class="form-control" placeholder="<?php esc_attr_e( 'Search &hellip;', 'alienship' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>">
                    </div>
                    <button id="searchsubmit" type="submit" name="submit" class="btn btn-default">Submit</button>
                </form>
            <?php endif; ?>
        </div>

    </div>
</nav>

<!-- End Main menu -->
