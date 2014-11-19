<?php
/**
 * Load Bootstrap javascript modules
 *
 * @package Waboot
 * @since 0.1.0.0
 */
function waboot_js_loader() {
	// Bootstrap JS components - Drop a custom build in your child theme's 'js' folder to override this one.
	wp_enqueue_script( 'bootstrap.js', waboot_locate_template_uri( 'assets/js/bootstrap.min.js' ), array( 'jquery' ), false, true );

	// Waboot Scripts
	if(WABOOT_ENV == "dev"){
		wp_enqueue_script( 'waboot-helper', waboot_locate_template_uri( 'sources/js/waboot-helper.js' ), array('jquery'),false, true);
	}else{
		wp_enqueue_script( 'waboot', waboot_locate_template_uri( 'assets/js/waboot.min.js' ), array('jquery','waboot-plugins'),false, true);
		wp_enqueue_script( 'waboot-plugins', waboot_locate_template_uri( 'assets/js/plugins.min.js' ), array('jquery'),false, true);
        if(is_child_theme()){
            $child_js = waboot_locate_template_uri( 'assets/js/waboot-child.min.js' );
            if($child_js != "")
                wp_enqueue_script( 'waboot-child.js', $child_js, array('jquery'),false, true);
        }
	}

    // Comment reply script
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) )
		wp_enqueue_script( 'comment-reply' );
}
add_action( 'wp_enqueue_scripts', 'waboot_js_loader', 90 );

function waboot_mobile_js_loader() {
    if(wb_is_mobile()){
        if(WABOOT_ENV == "dev"){
            wp_enqueue_script( 'waboot-helper-mobile', waboot_locate_template_uri( 'sources/js/waboot-helper-mobile.js' ), array('jquery','waboot-helper','offcanvas','fastclick','touchSwipe'),false, true);
            wp_enqueue_script( 'offcanvas', waboot_locate_template_uri( 'sources/js/vendor-mobile/offcanvas.js' ), array('jquery'),false, true);
            wp_enqueue_script( 'fastclick', waboot_locate_template_uri( 'sources/js/vendor-mobile/fastclick.js' ), array('jquery'),false, true);
            wp_enqueue_script( 'touchSwipe', waboot_locate_template_uri( 'sources/js/vendor-mobile/jquery.touchSwipe.js' ), array('jquery'),false, true);
        }else{
            wp_enqueue_script( 'waboot-mobile', waboot_locate_template_uri( 'assets/js/waboot-mobile.min.js' ), array('jquery','waboot','waboot-plugins','waboot-mobile-plugins'),false, true);
            wp_enqueue_script( 'waboot-mobile-plugins', waboot_locate_template_uri( 'assets/js/plugins-mobile.min.js' ), array('jquery'),false, true);
        }
    }
}
add_action( 'wp_enqueue_scripts', 'waboot_mobile_js_loader', 91 );

function waboot_ie_compatibility(){
    ?>
    <!--[if lt IE 9]>
        <script src="<?php echo get_template_directory_uri(); ?>/assets/js/html5shiv.min.js" type="text/javascript"></script>
        <script src="<?php echo get_template_directory_uri(); ?>/assets/js/respond.min.js" type="text/javascript"></script>
    <![endif]-->
    <?php
}
add_action("wp_head",'waboot_ie_compatibility');