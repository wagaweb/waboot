<?php
/**
Component Name: Admin Tweaks
Description: Tweak Wordpress Admin
Category: Utilities
Tags: Admin, Login, Media, Shortcodes
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
 */

class Admin_Tweaks extends \WBF\modules\components\Component {

	/**
	 * This method will be executed at Wordpress startup (every page load)
	 */
	public function setup(){
		parent::setup();


		/*
		 * * ******************* *
		 * | GENERAL HOOKS GROUP |
		 * * ******************* *
		 */

		if (!empty(\Waboot\functions\get_option($this->name.'_general_footer_message'))) {
			add_filter( 'admin_footer_text', [ $this, "general_footer_message" ] );
		}
		if (!empty(\Waboot\functions\get_option($this->name.'_general_extra_css'))) {
			add_action('admin_head', [$this,"general_extra_css"]);
		}
		if (\Waboot\functions\get_option($this->name.'_general_shortcodes') == true) {
			add_filter( 'widget_text', 'do_shortcode');
		}
		if (\Waboot\functions\get_option($this->name.'_general_shortcodes') == true) {
			add_action( 'admin_init', [$this,'hide_update_msg'], 1 );
		}
		if (\Waboot\functions\get_option($this->name.'_general_hide_wp_logo') == true) {
			add_action( 'admin_bar_menu', [$this,'remove_wp_logo'], 999 );
		}


		/*
		 * * ***************** *
		 * | LOGIN HOOKS GROUP |
		 * * ***************** *
		 */
		if (! empty(\Waboot\functions\get_option($this->name.'_login_logo_image'))) {
			add_action( 'login_enqueue_scripts', [$this,'login_logo_image'] );
		}
		if (! empty(\Waboot\functions\get_option($this->name.'_login_logo_url'))) {
			add_filter( 'login_headerurl', [$this,'login_logo_url'] );
		}
		if (! empty(\Waboot\functions\get_option($this->name.'_login_logo_title'))) {
			add_filter( 'login_headertitle', [$this,'login_logo_title'] );
		}
		if (! empty(\Waboot\functions\get_option($this->name.'_login_background_image'))) {
			add_action( 'login_head', [$this,'login_background_image'] );
		}


		/*
		 * * ********************* *
		 * | DASHBOARD HOOKS GROUP |
		 * * ********************* *
		 */

		if (\Waboot\functions\get_option($this->name.'_dashboard_add_cpt_rightnow') == true); {
			add_action('dashboard_glance_items', [$this,'dashboard_add_cpt_rightnow']);
		}
		if (\Waboot\functions\get_option($this->name.'_dashboard_hide_wp_widgets') == true); {
			add_action( 'wp_dashboard_setup', [$this,'dashboard_hide_wp_widgets'] );
		}
		if (\Waboot\functions\get_option($this->name.'_dashboard_content_for_admin') == true); {
			require_once 'widget/WpContentFolder.php';
			WpContentFolder::init('wpcontent');
		}
	}


	/**
	 * This method will be executed on the "wp" action in pages where the component must be loaded
	 */
	public function run(){
		parent::run();
	}


	/**
	 * Register component scripts (called automatically)
	 */
	public function scripts(){
		//wp_register_script('component-header_fixed', $this->directory_uri . '/assets/dist/js/headerFixed.js', ['jquery'], false, true);

		/*wp_localize_script('component-header_fixed', 'wbHeaderFixed', array(
			'company_name' => $company,
			'address' => $address,
			'mail' => $mail,
			'tel' => $tel,
		) );
		wp_enqueue_script('component-header_fixed');*/
	}


	/**
	 * Register component styles (called automatically)
	 */
	public function styles(){
		//wp_enqueue_style('component-header_fixed-style', $this->directory_uri . '/assets/dist/css/headerFixed.css');
	}


	/**
	 * Register component widgets (called automatically).
	 *
	 * @hooked 'widgets_init'
	 */
	public function widgets(){
		//register_widget("sampleWidget");
	}


	/**
	 * This is an action callback.
	 *
	 * Here you can use WBF Organizer to set component options
	 */
	public function register_options() {
		$orgzr = \WBF\modules\options\Organizer::getInstance();

		$orgzr->set_group("components");

		$section_name = $this->name."_component";
		$additional_params = [
			'component' => true,
			'component_name' => $this->name
		];

		$orgzr->add_section($section_name,$this->name." Component",null,$additional_params);
		$orgzr->set_section($section_name);



		/*
		 * * ******************** *
		 * | GENERAL TWEAKS GROUP |
		 * * ******************** *
		 */

		$orgzr->add([
			'type' => 'info',
			'name' => 'General Tweaks',
			'desc' => '',
		],null,null,$additional_params);
		$orgzr->add([
			'name' => __( 'Footer Message', 'waboot' ),
			'desc' => __( '', 'waboot' ),
			'id'   => $this->name.'_general_footer_message',
			'std'  => __( 'Thank you for creating with <a href="http://waboot.org/">Waboot</a>', 'waboot'),
			'type' => 'textarea'
		],null,null,$additional_params);
		$orgzr->add([
			'name' => __( 'Extra CSS for Admin', 'waboot' ),
			'desc' => __( 'put your css on the same line', 'waboot' ),
			'id'   => $this->name.'_general_extra_css',
			'type' => 'csseditor'
		],null,null,$additional_params);
		$orgzr->add([
			'name' => __( 'Enable Shortcodes Everywhere', 'waboot' ),
			'desc' => __( '', 'waboot' ),
			'id'   => $this->name.'_general_shortcodes',
			'std'  => '',
			'type' => 'checkbox'
		],null,null,$additional_params);
		$orgzr->add([
			'name' => __( 'Block WordPress upgrade notice for non-admins', 'waboot' ),
			'desc' => __( '', 'waboot' ),
			'id'   => $this->name.'_general_block_upgrade_notice',
			'std'  => '',
			'type' => 'checkbox'
		],null,null,$additional_params);
		$orgzr->add([
			'name' => __( 'Hide WP logo', 'waboot' ),
			'desc' => __( '', 'waboot' ),
			'id'   => $this->name.'_general_hide_wp_logo',
			'std'  => '',
			'type' => 'checkbox'
		],null,null,$additional_params);



		/*
		 * * ****************** *
		 * | LOGIN SCREEN GROUP |
		 * * ****************** *
		 */

		$orgzr->add([
			'type' => 'info',
			'name' => 'Login Screen',
			'desc' => __('customize login screen of your theme', 'waboot')
		],null,null,$additional_params);
		$orgzr->add([
			'name' => __( 'Logo', 'waboot' ),
			'desc' => __( '', 'waboot' ),
			'id'   => $this->name.'_login_logo_image',
			'std'  => '',
			'type' => 'upload'
		],null,null,$additional_params);
		$orgzr->add([
			'name' => __( 'Logo Url', 'waboot' ),
			'desc' => __( '', 'waboot' ),
			'id'   => $this->name.'_login_logo_url',
			'std'  => '',
			'type' => 'text'
		],null,null,$additional_params);
		$orgzr->add([
			'name' => __( 'Logo Title', 'waboot' ),
			'desc' => __( '', 'waboot' ),
			'id'   => $this->name.'_login_logo_title',
			'std'  => '',
			'type' => 'text'
		],null,null,$additional_params);
		$orgzr->add([
			'name' => __( 'Custom CSS', 'waboot' ),
			'desc' => __( '', 'waboot' ),
			'id'   => $this->name.'_login_custom_css',
			'std'  => '',
			'type' => 'csseditor'
		],null,null,$additional_params);
		$orgzr->add([
			'name' => __( 'Background Image', 'waboot' ),
			'desc' => __( '', 'waboot' ),
			'id'   => $this->name.'_login_background_image',
			'type' => 'upload'
		],null,null,$additional_params);



		/*
		 * * ********************** *
		 * | DASHBOARD TWEAKS GROUP |
		 * * ********************** *
		 */
		$orgzr->add([
			'type' => 'info',
			'name' => 'Dashboard Tweaks',
			'desc' => 'Customize the dashboard'
		],null,null,$additional_params);
		$orgzr->add([
			'name' => __( 'Add Custom Post Types to Right Now Widget', 'waboot' ),
			'desc' => __( '', 'waboot' ),
			'id'   => $this->name.'_dashboard_add_cpt_rightnow',
			'std'  => '',
			'type' => 'checkbox'
		],null,null,$additional_params);
		$orgzr->add([
			'name' => __( 'Hide quickpress, incoming link, wordpress blog, other wordpress link widgets', 'waboot' ),
			'desc' => __( '', 'waboot' ),
			'id'   => $this->name.'_dashboard_hide_wp_widgets',
			'std'  => '',
			'type' => 'checkbox'
		],null,null,$additional_params);
		$orgzr->add([
			'name' => __( 'Wp-content widget only for admin', 'waboot' ),
			'desc' => __( '', 'waboot' ),
			'id'   => $this->name.'_dashboard_content_for_admin',
			'std'  => '',
			'type' => 'checkbox'
		],null,null,$additional_params);



		/*
		 * * ****************** *
		 * | MEDIA TWEAKS GROUP |
		 * * ****************** *
		 */
	/*	$orgzr->add([
			'type' => 'info',
			'name' => 'Media Tweaks'
		],null,null,$additional_params);
		$orgzr->add([
			'name' => __( 'Bigger thumbnails in the default column', 'waboot' ),
			'desc' => __( '', 'waboot' ),
			'id'   => $this->name.'_media_bigger_thumb',
			'std'  => '',
			'type' => 'checkbox'
		],null,null,$additional_params);
		$orgzr->add([
			'name' => __( 'Add image size column', 'waboot' ),
			'desc' => __( '', 'waboot' ),
			'id'   => $this->name.'_media_add_image_size',
			'std'  => '',
			'type' => 'checkbox'
		],null,null,$additional_params);
		$orgzr->add([
			'name' => __( 'Add a column that lists all thumbnails of the image, with direct link to it.', 'waboot' ),
			'desc' => __( '', 'waboot' ),
			'id'   => $this->name.'_media_add_thumbnail_column',
			'std'  => '',
			'type' => 'checkbox'
		],null,null,$additional_params);
		$orgzr->add([
			'name' => __( 'Download link in row actions', 'waboot' ),
			'desc' => __( '', 'waboot' ),
			'id'   => $this->name.'_media_download_link',
			'std'  => '',
			'type' => 'checkbox'
		],null,null,$additional_params);
		$orgzr->add([
			'name' => __( 'Remove Meta Boxes (Discussion, Comments)', 'waboot' ),
			'desc' => __( '', 'waboot' ),
			'id'   => $this->name.'_media_remove_metaboxes',
			'std'  => '',
			'type' => 'checkbox'
		],null,null,$additional_params);
		$orgzr->add([
			'name' => __( 'Include all custom sizes', 'waboot' ),
			'desc' => __( '', 'waboot' ),
			'id'   => $this->name.'_media_include_sizes',
			'std'  => '',
			'type' => 'checkbox'
		],null,null,$additional_params);
		$orgzr->add([
			'name' => __( 'Sharpen resized images (only jpg)', 'waboot' ),
			'desc' => __( '', 'waboot' ),
			'id'   => $this->name.'_media_sharpen_resized',
			'std'  => '',
			'type' => 'checkbox'
		],null,null,$additional_params);

*/

		$orgzr->reset_group();
		$orgzr->reset_section();
	}

	public function onActivate(){
		parent::onActivate();
		//Do stuff...
	}

	public function onDeactivate(){
		parent::onDeactivate();
		//Do stuff...
	}



	/*
	 * * ******************** *
	 * | GENERAL TWEAKS GROUP |
	 * * ******************** *
	 */

	/*
	 * hooked: admin_footer_text
	 */
	public function general_footer_message() {
		echo \Waboot\functions\get_option($this->name.'_general_footer_message');
	}

	/*
	 * hooked: admin_head
	 */
	public function general_extra_css() {
		$css = \Waboot\functions\get_option($this->name.'_general_extra_css');
		echo '<style type="text/css">'.$css.'</style>';
	}

	/*
	 * hoked: admin_init
	 * priority: 1
	 */
	public function hide_update_msg(){
		! current_user_can( 'install_plugins' )
		and remove_action( 'admin_notices', 'update_nag', 3 );
	}

	/*
	 * hooked: admin_bar_menu
	 * priority: 999
	 */
	public function remove_wp_logo( $wp_admin_bar ) {
		$wp_admin_bar->remove_node( 'wp-logo' );
	}



	/*
	 * * ******************** *
	 * | LOGIN TWEAKS GROUP |
	 * * ******************** *
	 */

	/*
	 * Hooked: login_enqueue_scripts
	 */
	public function login_logo_image() {
		$url = \Waboot\functions\get_option($this->name.'_login_logo_image');
		echo '<style type="text/css">
			#login h1 a, .login h1 a {
				background-image: url('.$url.');
				padding-bottom: 30px;
			}
		</style>';
	}

	/*
	 * Hooked: login_headerurl
	 */
	public function login_logo_url() {
		return \Waboot\functions\get_option($this->name.'_login_logo_url');
	}

	/*
	 * Hooked: login_headertitle
	 */
	function login_logo_title() {
		return \Waboot\functions\get_option($this->name.'_login_logo_title');
	}

	/*
	 * Hooked: login_head
	 */
	public function login_background_image(){
		$url = \Waboot\functions\get_option($this->name.'_login_background_image');
		echo '<style type="text/css">
			body.login {
				background-image: url('.$url.');
				padding-bottom: 30px;
			}
		</style>';
	}



	/*
	 * * ************************ *
	 * | DASHBOARD FUNCTION GROUP |
	 * * ************************ *
	 */


	/*
	 * Hooked: dashboard_glance_items
	 */
	public function dashboard_add_cpt_rightnow() {
		$glances = array();
		$args = array(
			'public'   => true,  // Showing public post types only
			'_builtin' => false  // Except the build-in wp post types (page, post, attachments)
		);
		// Getting your custom post types
		$post_types = get_post_types($args, 'object', 'and');
		foreach ($post_types as $post_type)
		{
			// Counting each post
			$num_posts = wp_count_posts($post_type->name);
			// Number format
			$num   = number_format_i18n($num_posts->publish);
			// Text format
			$text  = _n($post_type->labels->singular_name, $post_type->labels->name, intval($num_posts->publish));
			// If use capable to edit the post type
			if (current_user_can('edit_posts'))
			{
				// Show with link
				$glance = '<a class="'.$post_type->name.'-count" href="'.admin_url('edit.php?post_type='.$post_type->name).'">'.$num.' '.$text.'</a>';
			}
			else
			{
				// Show without link
				$glance = '<span class="'.$post_type->name.'-count">'.$num.' '.$text.'</span>';
			}
			// Save in array
			$glances[] = $glance;
		}
		// return them
		return $glances;
	}

	/*
	 * Hooked: 'wp_dashboard_setup'
	 */
	public function dashboard_hide_wp_widgets() {
		//remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );   // Right Now
		//remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' ); // Recent Comments
		//remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );  // Incoming Links
		//remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );   // Plugins
		remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );  // Quick Press
		//remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );  // Recent Drafts
		remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );   // WordPress blog
		remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );   // Other WordPress News
		// use 'dashboard-network' as the second parameter to remove widgets from a network dashboard.
	}

}