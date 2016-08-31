<?php
/**
 * Load Bootstrap javascript modules
 *
 * @package Waboot
 * @since 0.1.0.0
 */
function waboot_js_loader() {
	wp_enqueue_script( 'bootstrap.js', wbf_locate_template_uri( 'assets/dist/js/bootstrap.min.js' ), array( 'jquery' ), false, true ); // Bootstrap JS components - Drop a custom build in your child theme's 'js' folder to override this one.
	wp_enqueue_script( 'offcanvas', wbf_locate_template_uri( 'assets/src/js/vendor/offcanvas.js' )."#asyncload", array( 'jquery' ), false, true );
	waboot_enqueue_main_script();
    // Comment reply script
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) )
		wp_enqueue_script( 'comment-reply' );
}
add_action( 'wp_enqueue_scripts', 'waboot_js_loader', 90 );
add_action( 'admin_enqueue_scripts', 'waboot_enqueue_main_script', 90 );

function waboot_enqueue_main_script(){
	global $wpdb;

	$received_mails = is_admin() ? $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."wb_mails",ARRAY_A) : false;
	if($received_mails){
		foreach($received_mails as $k => $m){
			$received_mails[$k]['sender_info'] = unserialize($m['sender_info']);
		}
	}

	$wpData = apply_filters("wbft/js/localization",array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'wpurl' => get_bloginfo('wpurl'),
			'isMobile' => wbft_wbf_in_use() ? wb_is_mobile() : null,
			'isAdmin' => is_admin(),
			'isDebug' => WP_DEBUG || WABOOT_ENV == "dev" || WBF_ENV == "dev",
			'wp_screen' => function_exists("get_current_screen") ? get_current_screen() : null,
			'components' => isset($GLOBALS['loaded_components']) ? $GLOBALS['loaded_components'] : null,
			'components_js' => call_user_func(function(){
				global $loaded_components;
				if(!isset($loaded_components)) return null;
				foreach($loaded_components as $c){
					$dir = dirname($c->file);
					$js = $dir."/".$c->name."-module.js";
					if(!file_exists($js)) $js = $dir."/assets/js/".$c->name."-module.js";
					if(!file_exists($js)) $js = $dir."/js/".$c->name."-module.js";
					if(!file_exists($js)) continue;
					$components_js[] = $js;
				}
				if(empty($components_js)) return null;
				return $components_js;
			}),
			'contactForm' => array(
				'contact_email_subject' => __("New Email from site","waboot"),
				'labels' => array(
					'success' => __("You email was send successfully. Thanks!","waboot"),
					'warning' => __("We are sorry: due technical difficulties we was unable to send your message correctly.","waboot"),
					'error'   => __("We are sorry: an error happens when sending your message.","waboot"),
					'errors'  => array(
						'isEmpty' => __("This field cannot be empty","waboot"),
						'isNotChecked' => __("This field needs to be checked","waboot"),
						'_default_' => __("An error was triggered by this field","waboot"),
					)
				),
				'mails' => json_encode($received_mails),
				'recipient' => call_user_func(function(){
					global $post;
					switch(apply_filters("wbft/contact_form/recipient/type",of_get_option("contact_form_mail_recipient_type","admin"))){
						case "author":
							$to = array(
								'id' => isset($post->ID) && $post->ID != 0 ? $post->post_author : 0,
								'name' => isset($post->ID) && $post->ID != 0 ? get_the_author_meta('display_name' , $post->post_author) : "",
								'email' => isset($post->ID) && $post->ID != 0 ? get_the_author_meta( 'user_email' , $post->post_author) : ""
							);
							break;
						case "specific_contact":
							$to = array(
								'id' => 0,
								'name' => "Site Admin", //of_get_option("contact_form_mail_recipient_name",""),
								'email' => of_get_option("contact_form_mail_recipient_email",""),
							);
							break;
						case "admin":
						default:
							$to = array(
								'id' => 1,
								'name' => "Site Admin",
								'email' => get_option("admin_email")
							);
							break;
					}
					return $to;
				})
			)
		)
	);

	$deps = array('jquery','jquery-ui-core','jquery-ui-dialog','backbone','underscore');

	if( (defined('WABOOT_ENV') && WABOOT_ENV == "dev") || (!defined('WABOOT_ENV') && WP_DEBUG) ) {
		wp_register_script( 'waboot', wbf_locate_template_uri( 'assets/src/js/waboot.js' )."#asyncload", $deps,false, true);
		$child_js = is_child_theme() ? wbf_locate_template_uri( 'assets/src/js/waboot-child.js' ) : false;
	}else{
		if(is_file(get_template_directory()."/assets/dist/js/waboot.min.js")){
			wp_enqueue_script( 'waboot', wbf_locate_template_uri( 'assets/dist/js/waboot.min.js' )."#asyncload", $deps,false, true);
		}else{
			wp_enqueue_script( 'waboot', wbf_locate_template_uri( 'assets/src/js/waboot.js' )."#asyncload", $deps,false, true); //Load the source file if minified is not available
		}
		$child_js = is_child_theme() ? wbf_locate_template_uri( 'assets/dist/js/waboot-child.min.js' ) : false;
	}

	wp_localize_script( 'waboot', 'wbData', $wpData);
	wp_enqueue_script( 'waboot');
	if($child_js != ""){
		wp_enqueue_script( 'waboot-child', $child_js."#asyncload", array('jquery'),false, true);
	}

	if(is_admin()){
		$screen = get_current_screen();
		if($screen->base == "waboot-0_page_waboot_inbox"){
			wp_enqueue_style('jquery-ui-style');
		}
	}
}

function waboot_ie_compatibility(){
    ?>
    <!--[if lt IE 9]>
        <script src="<?php echo get_template_directory_uri(); ?>/assets/dist/js/html5shiv.min.js" type="text/javascript"></script>
        <script src="<?php echo get_template_directory_uri(); ?>/assets/dist/js/respond.min.js" type="text/javascript"></script>
    <![endif]-->
    <?php
}
add_action("wp_head",'waboot_ie_compatibility');