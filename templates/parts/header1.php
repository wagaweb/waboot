<?php
/**
 * The template used to load the Main Menu in header*.php
 *
 * @package Waboot
 * @since Waboot 1.0
 */
?>
<!-- Main menu -->

<div id="header-1" class="<?php echo apply_filters( 'waboot_main_navbar_class' , '' ); ?>">

    <div class="row header-blocks hidden-sm hidden-xs">
        <div id="header-left" class="col-md-3 vcenter">
            <?php if ( of_get_option('waboot_social_position') === 'header-left' ) { include 'social-widget.php'; } ?>
            <?php dynamic_sidebar( 'header-left' ); ?>
        </div><!--
        --><div id="logo" class="col-md-6 vcenter">
            <?php if ( of_get_option( 'waboot_logo_in_navbar' ) ) : ?>
                <a href="<?php echo home_url( '/' ); ?>"><img src="<?php echo of_get_option( 'waboot_logo_in_navbar' ); ?>"> </a>
            <?php else : ?>
                <?php
                do_action( 'waboot_site_title' );
                // do_action( 'waboot_site_description' );
                ?>
            <?php endif; ?>
        </div><!--
        --><div id="header-right" class="col-md-3 vcenter">
            <?php if ( of_get_option('waboot_social_position') === 'header-right' ) { include 'social-widget.php'; } ?>
            <?php dynamic_sidebar( 'header-right' ); ?>
        </div>
    </div>

    <nav class="navbar navbar-default main-navigation" role="navigation">

        <?php get_template_part('/templates/parts/nav-main'); ?>

        <?php if ( of_get_option('waboot_mobilenav_style') === 'offcanvas' ) { include 'nav-offcanvas.php'; } ?>

    </nav>

</div>

<!-- End Main menu -->
