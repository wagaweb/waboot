<?php
/**
 * The template used to load the Header in header*.php
 *
 * @package Waboot
 * @since Waboot 1.0
 */
?>
<!-- Header 1 -->

    <div class="row header-blocks hidden-sm hidden-xs">
        <div id="header-left" class="col-md-3 vcenter">
            <?php if ( of_get_option('waboot_social_position') === 'header-left' && of_get_option("social_position_none") != 1 ) { get_template_part('templates/parts/social-widget'); } ?>
            <?php dynamic_sidebar( 'header-left' ); ?>
        </div><!--
        --><div id="logo" class="col-md-6 vcenter">
            <?php if ( waboot_get_desktop_logo() != "" ) : ?>
	            <?php waboot_desktop_logo(); ?>
            <?php else : ?>
                <?php
                    do_action( 'waboot_site_title' );
	                do_action( 'waboot_site_description' );
	            ?>
            <?php endif; ?>
        </div><!--
        --><div id="header-right" class="col-md-3 vcenter">
            <?php if ( of_get_option('waboot_social_position') === 'header-right' && of_get_option("social_position_none") != 1 ) { get_template_part('templates/parts/social-widget'); } ?>
            <?php dynamic_sidebar( 'header-right' ); ?>
        </div>
    </div>

<!-- End Header 1 -->
