<?php
/**
 * The template used to load the Main Menu in header*.php
 *
 * @package Waboot
 * @since Alien Ship 0.70
 */
?>
    <!-- Main menu -->
    <?php if (of_get_option('waboot_float_navbar', 1) ) : ?>
		<nav id="navbar-2" class="<?php echo apply_filters( 'alienship_main_navbar_class' , 'navbar navbar-default main-navigation' ); ?>" role="navigation">

            <div id="logo">
            <?php if ( of_get_option( 'waboot_logo_in_navbar' ) ) : ?>
                <a href="<?php echo home_url( '/' ); ?>"><img src="<?php echo of_get_option( 'waboot_logo_in_navbar' ); ?>"> </a>
            <?php else : ?>
                <?php
                    do_action( 'alienship_site_title' );
                    do_action( 'alienship_site_description' );
                ?>
            <?php endif; ?>
            </div>
		
            <?php if ( is_active_sidebar( 'header-right' ) || of_get_option('waboot_social_position', 'header-right') == 'header-right' ) : ?>
            <div id="header-right">
                <?php if ( of_get_option('waboot_social_position') === 'header-right' ) { include 'social-widget.php'; } ?>
                <?php dynamic_sidebar( 'header-right' ); ?>
            </div>
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

            <div class="collapse navbar-collapse navbar-ex2-collapse navbar-right">
                <?php wp_nav_menu( array(
                    'theme_location' => 'main',
                    'depth'          => 2,
                    'container'      => false,
                    'menu_class'     => 'nav navbar-nav',
                    'walker'         => new waboot_bootstrap_navwalker(),
                    'fallback_cb'    => 'waboot_bootstrap_navwalker::fallback'
                    )
                ); ?>

                <?php if ( of_get_option( 'waboot_search_bar', '1' ) ) : ?>
                    <form id="searchform" class="navbar-form navbar-right" role="search" action="<?php echo site_url(); ?>" method="get">
                        <div class="form-group">
                            <input id="s" name="s" type="text" class="form-control" placeholder="<?php esc_attr_e( 'Search &hellip;', 'alienship' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>">
                        </div>
                        <button id="searchsubmit" type="submit" name="submit" class="btn btn-default">Submit</button>
                    </form>
                <?php endif; ?>
            </div>
	    </nav>
    <?php else : ?>
		<nav id="navbar-1" class="<?php echo apply_filters( 'alienship_main_navbar_class' , 'navbar navbar-default main-navigation' ); ?>" role="navigation">
            <?php if ( is_active_sidebar( 'header-left' ) or of_get_option('waboot_social_position') === 'header-left' ) : ?>
                <div id="header-left">
                    <?php if ( of_get_option('waboot_social_position') === 'header-left' ) { include 'social-widget.php'; } ?>
                    <?php dynamic_sidebar( 'header-left' ); ?>
                </div>
            <?php endif; ?>

            <?php if ( is_active_sidebar( 'header-right' ) or of_get_option('waboot_social_position', 'header-right') === 'header-right' ) : ?>
                <div id="header-right">
                    <?php if ( of_get_option('waboot_social_position') === 'header-right' ) { include 'social-widget.php'; } ?>
                    <?php dynamic_sidebar( 'header-right' ); ?>
                </div>
            <?php endif; ?>

			<div id="logo">
			<?php if ( of_get_option( 'waboot_logo_in_navbar' ) ) : ?>
			    <a href="<?php echo home_url( '/' ); ?>"><img src="<?php echo of_get_option( 'waboot_logo_in_navbar' ); ?>"> </a>
		    <?php else : ?>
                <?php
                    do_action( 'waboot_site_title' );
                    do_action( 'waboot_site_description' );
                ?>
		    <?php endif; ?>
		    </div>

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
	    </nav>
    <?php endif; ?>
    <!-- End Main menu -->
