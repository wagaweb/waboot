<?php
/**
 * The template used to load the Mobile Menu in header*.php
 *
 * @package Waboot
 * @since Waboot 1.0
 */
?>

<!-- Main Nav -->
<div class="navbar-header">

    <?php if ( of_get_option('waboot_mobilenav_style') === 'offcanvas' ) : ?>
        <button type="button" class="navbar-toggle" data-toggle="offcanvas" data-target=".navbar-mobile-collapse" data-canvas="body">
    <?php else : ?>
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-main-collapse">
    <?php endif; ?>
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
    </button>

    <?php if ( waboot_get_desktop_logo() != "" ) : ?>
        <a class="navbar-brand" href="<?php echo home_url( '/' ); ?>">
            <?php waboot_desktop_logo(); ?>
        </a>
    <?php else : ?>
        <?php waboot_site_title(); ?>
    <?php endif; ?>

</div>

<div class="collapse navbar-collapse navbar-main-collapse">

    <?php if ( of_get_option('waboot_social_position') === 'navigation' && of_get_option("social_position_none") != 1 ) { get_template_part('templates/parts/social-widget'); } ?>

    <?php if ( of_get_option( 'waboot_search_bar', '1' ) ) : ?>
        <form id="searchform" class="navbar-form navbar-right" role="search" action="<?php echo site_url(); ?>" method="get">
            <div class="form-group">
                <input id="s" name="s" type="text" class="form-control" placeholder="<?php esc_attr_e( 'Search &hellip;', 'waboot' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>">
            </div>
            <button id="searchsubmit" type="submit" name="submit" class="btn btn-default">Submit</button>
        </form>
    <?php endif; ?>

    <?php wp_nav_menu( array(
            'theme_location' => 'main',
            'depth'          => 0,
            'container'      => false,
            'menu_class'     => apply_filters('waboot_mainnav_class', array('nav', 'navbar-nav')),
            'walker'	     => class_exists('WabootNavMenuWalker') ? new WabootNavMenuWalker() : "", //todo: includere in Waboot on in wbf?
            'fallback_cb' => 'waboot_nav_menu_fallback'
        )
    ); ?>

</div>
<!-- End Main Nav -->
