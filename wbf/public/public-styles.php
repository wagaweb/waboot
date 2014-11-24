<?php
/**
 * Register and enqueue the front end CSS
 *
 * @package Waboot
 * @since 0.1.0
 */

/**
 * Apply custom in-line styles in header
 */
function waboot_theme_options_header_styles()
{
    ?>
    <style type="text/css">
        body {
            background-color: <?php echo of_get_option( 'waboot_body_bgcolor' ); ?> !important;
            background-image: url(<?php echo of_get_option( 'waboot_body_bgimage' ); ?>);
            background-repeat: <?php echo of_get_option( 'waboot_body_bgrepeat' ); ?>;
            background-position: <?php echo of_get_option( 'waboot_body_bgpos' ); ?>;
            background-attachment: <?php echo of_get_option( 'waboot_body_bgattach' ); ?>;
        }

        #topnav-wrapper {
            background-color: <?php echo of_get_option( 'waboot_topnav_bgcolor' ); ?>;
        }

        #header-wrapper {
            background-color: <?php echo of_get_option( 'waboot_header_bgcolor' ); ?>;
        }

        #banner-wrapper {
            background-color: <?php echo of_get_option( 'waboot_banner_bgcolor' ); ?>;
        }

        #content-wrapper {
            background-color: <?php echo of_get_option( 'waboot_content_bgcolor' ); ?>;
        }

        #contentbottom-wrapper {
            background-color: <?php echo of_get_option( 'waboot_bottom_bgcolor' ); ?>;
        }

        #footer-wrapper {
            background-color: <?php echo of_get_option( 'waboot_footer_bgcolor' ); ?>;
        }

        #page {
            background-color: <?php echo of_get_option( 'waboot_page_bgcolor' ); ?>;
        }

        .navbar.main-navigation .navbar-collapse {
            background-color: <?php echo of_get_option( 'waboot_navbar_bgcolor' ); ?>;
        }

    </style>
<?php
}

add_action("waboot_head", 'waboot_theme_options_header_styles');