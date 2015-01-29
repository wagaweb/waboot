<?php
/**
 * The template used to load the Main Menu in header*.php
 *
 * @package Waboot
 * @since Waboot 1.0
 */
?>
<!-- Main menu -->

<div id="header-3" class="<?php echo apply_filters( 'waboot_main_navbar_class' , '' ); ?>">

    <nav class="navbar navbar-default main-navigation" role="navigation">

        <?php get_template_part('/templates/parts/nav-main'); ?>

        <?php if ( of_get_option('waboot_mobilenav_style') === 'offcanvas' ) { include 'nav-offcanvas.php'; } ?>

    </nav>

</div>

<!-- End Main menu -->
