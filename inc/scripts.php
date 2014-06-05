<?php
/**
 * Load Bootstrap javascript modules
 *
 * @package Waboot
 * @since 1.0.0
 */
function waboot_bootstrap_js_loader() {

	// Bootstrap JS components - Drop a custom build in your child theme's 'js' folder to override this one.
	wp_enqueue_script( 'bootstrap.js', waboot_locate_template_uri( 'assets/js/bootstrap.min.js' ), array( 'jquery' ), '3.0.2', true );

	// Waboot Scripts
	if(CURRENT_ENV == ENV_DEV){
		wp_enqueue_script( 'waboot-helper.js', waboot_locate_template_uri( 'sources/js/waboot-helper.js' ), array('jquery'),false, true);
		// wp_enqueue_script( 'dropdown-toggle.js', waboot_locate_template_uri( 'sources/js/dropdown-toggle.js' ), array('jquery'),false, true);
	}else{
		wp_enqueue_script( 'waboot.js', waboot_locate_template_uri( 'assets/js/waboot.min.js' ), array('jquery'),false, true);
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
add_action( 'wp_enqueue_scripts', 'waboot_bootstrap_js_loader' );

function waboot_ie_compatibility(){
    if(CURRENT_ENV == ENV_DEV){
        wp_enqueue_script( 'ie-compatibility.js', waboot_locate_template_uri( 'sources/js/ie-compatibility.js' ),'1.0.0', true);
    }
    ?>
    <!--[if lt IE 9]>
        <script src="<?php echo get_template_directory_uri(); ?>/assets/js/html5shiv.min.js" type="text/javascript"></script>
        <script src="<?php echo get_template_directory_uri(); ?>/assets/js/respond.min.js" type="text/javascript"></script>
    <![endif]-->
    <?php
}
add_action("wp_head",'waboot_ie_compatibility');

function waboot_less_compiler_js(){
    wp_enqueue_script( 'waboot-less-compiler.js', waboot_locate_template_uri( 'sources/js/waboot-less-compiler.js' ),'1.0.0', true);
}
//add_action( 'wp_enqueue_scripts', 'waboot_less_compiler_js' );