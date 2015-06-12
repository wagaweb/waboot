<?php
global $woocommerce;

if(isset($woocommerce)){
	//Declare WooCommerce support
	add_action( 'after_setup_theme', 'woocommerce_support' );
	function woocommerce_support() {
		add_theme_support( 'woocommerce' );
	}
	//Setup the wrapper
	remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
	remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
	add_action('woocommerce_before_main_content', 'waboot_wrapper_start', 10);
	add_action('woocommerce_after_main_content', 'waboot_wrapper_end', 10);
	function waboot_wrapper_start() {
		?>
		<div id="main-wrap" class="<?php echo apply_filters( 'waboot_mainwrap_container_class', 'content-area col-sm-8' ); ?>"><main id="main" class="site-main" role="main">
		<?php
	}
	function waboot_wrapper_end() {
		echo '</main></div>';
	}
	//Disabling actions
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
    remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
}