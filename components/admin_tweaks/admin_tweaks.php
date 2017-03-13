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

if(!class_exists("\\WBF\\modules\\components\\Component")) return;

class Admin_Tweaks extends \WBF\modules\components\Component {
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

		/**
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
			'std'  => __( 'Thank you for creating with <a href="http://waboot.io/">Waboot</a>', 'waboot'),
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


		/**
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
			'name' => __( 'Logo height', 'waboot' ),
			'desc' => __( '', 'waboot' ),
			'id'   => $this->name.'_login_logo_height',
			'std'  => '',
			'type' => 'text'
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


		/**
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


		/**
		 * * ****************** *
		 * | MEDIA TWEAKS GROUP |
		 * * ****************** *
		 */
		$orgzr->add([
			'type' => 'info',
			'name' => 'Media Tweaks',
			'desc' => 'Customize Media'
		],null,null,$additional_params);
		$orgzr->add([
			'name' => __( 'Allow svg upload and add svg support for dimensions', 'waboot' ),
			'desc' => __( '', 'waboot' ),
			'id'   => $this->name.'_media_allow_svg_upload',
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
		$orgzr->add([
			'name' => __( 'Wraps the content of a WordPress media gallery in a Twitter\'s Bootstrap grid', 'waboot' ),
			'desc' => __( '', 'waboot' ),
			'id'   => $this->name.'_media_bootstrap_galleries',
			'std'  => '',
			'type' => 'checkbox'
		],null,null,$additional_params);
		$orgzr->add([
			'name' => __( 'Bigger thumbnails in the default column', 'waboot' ),
			'desc' => __( '', 'waboot' ),
			'id'   => $this->name.'_media_bigger_thumbs',
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
			'name' => __( 'Remove Meta Boxes (Discussion, Comments)', 'waboot' ),
			'desc' => __( '', 'waboot' ),
			'id'   => $this->name.'_media_remove_metaboxes',
			'std'  => '',
			'type' => 'multicheck',
			'options' => [
				'author'        => 'Author',
				'comments'      => 'Comments',
				'slug'          => 'Slug',
				'discussion'    => 'Discussion'
			]
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


		$orgzr->reset_group();
		$orgzr->reset_section();
	}
	/**
	 * This method will be executed at Wordpress startup (every page load)
	 */
	public function setup() {
		parent::setup();


		/**
		 * * ******************* *
		 * | GENERAL HOOKS GROUP |
		 * * ******************* *
		 */
		if ( ! empty( \Waboot\functions\get_option( $this->name . '_general_footer_message' ) ) ) {
			add_filter( 'admin_footer_text', [ $this, "general_footer_message" ] );
		}
		if ( ! empty( \Waboot\functions\get_option( $this->name . '_general_extra_css' ) ) ) {
			add_action( 'admin_head', [ $this, "general_extra_css" ] );
		}
		if ( \Waboot\functions\get_option( $this->name . '_general_shortcodes' ) == true ) {
			add_filter( 'widget_text', 'do_shortcode' );
		}
		if ( \Waboot\functions\get_option( $this->name . '_general_shortcodes' ) == true ) {
			add_action( 'admin_init', [ $this, 'hide_update_msg' ], 1 );
		}
		if ( \Waboot\functions\get_option( $this->name . '_general_hide_wp_logo' ) == true ) {
			add_action( 'admin_bar_menu', [ $this, 'remove_wp_logo' ], 999 );
		}


		/**
		 * * ***************** *
		 * | LOGIN HOOKS GROUP |
		 * * ***************** *
		 */
		if ( ! empty( \Waboot\functions\get_option( $this->name . '_login_logo_image' ) ) ) {
			add_action( 'login_enqueue_scripts', [ $this, 'login_logo' ] );
		}
		if ( ! empty( \Waboot\functions\get_option( $this->name . '_login_logo_height' ) ) ) {
			add_action( 'login_enqueue_scripts', [ $this, 'login_logo' ] );
		}
		if ( ! empty( \Waboot\functions\get_option( $this->name . '_login_logo_url' ) ) ) {
			add_filter( 'login_headerurl', [ $this, 'login_logo_url' ] );
		}
		if ( ! empty( \Waboot\functions\get_option( $this->name . '_login_logo_title' ) ) ) {
			add_filter( 'login_headertitle', [ $this, 'login_logo_title' ] );
		}
		if ( ! empty( \Waboot\functions\get_option( $this->name . '_login_custom_css' ) ) ) {
			add_action( 'login_head', [ $this, "login_custom_css" ] );
		}
		if ( ! empty( \Waboot\functions\get_option( $this->name . '_login_background_image' ) ) ) {
			add_action( 'login_head', [ $this, 'login_background_image' ] );
		}

		/**
		 * * ********************* *
		 * | DASHBOARD HOOKS GROUP |
		 * * ********************* *
		 */
		if ( \Waboot\functions\get_option( $this->name . '_dashboard_add_cpt_rightnow' ) == true ) {
			add_action( 'dashboard_glance_items', [ $this, 'dashboard_add_cpt_rightnow' ] );
		}
		if ( \Waboot\functions\get_option( $this->name . '_dashboard_hide_wp_widgets' ) == true ) {
			add_action( 'wp_dashboard_setup', [ $this, 'dashboard_hide_wp_widgets' ] );
		}


		/**
		 * * ***************** *
		 * | MEDIA HOOKS GROUP |
		 * * ***************** *
		 */
		if ( \Waboot\functions\get_option( $this->name . '_media_allow_svg_upload' ) == true ) {
			add_filter( 'upload_mimes', [ $this, 'media_allow_svg_uploads' ]);
			add_filter( 'wp_prepare_attachment_for_js', [ $this, 'media_allow_svg_adjust_response'], 10, 3 );
			add_action( 'admin_enqueue_scripts', [ $this, 'media_allow_svg_administration_styles' ]);
			add_action( 'wp_head', [ $this, 'media_allow_svg_public_styles' ]);
		}
		if ( \Waboot\functions\get_option( $this->name . '_media_sharpen_resized' ) == true ) {
			add_filter( 'image_make_intermediate_size', [ $this, 'media_sharpen_resized' ] );
		}
		if ( \Waboot\functions\get_option( $this->name . '_media_bootstrap_galleries' ) == true ) {
			require_once 'includes/bootstrap_galleries/BootstrapGalleries.php';
			add_filter( 'post_gallery', [ 'BootstrapGalleries', 'media_bootstrap_galleries' ], 10, 2 );
		}

		if ( \Waboot\functions\get_option( $this->name . '_media_bigger_thumbs' ) == true ) {
			add_action( 'admin_head', [ $this, 'media_bigger_thumbs' ] );
		}
		if ( \Waboot\functions\get_option( $this->name . '_media_include_sizes' ) == true ) {
			add_filter( 'image_size_names_choose', [ $this, 'media_include_sizes' ] );
		}
		if ( ! empty(\Waboot\functions\get_option( $this->name . '_media_remove_metaboxes' )) ) {
			add_filter( 'add_meta_boxes', [ $this, 'media_remove_metaboxes' ] );
		}
		if ( ! empty(\Waboot\functions\get_option( $this->name . '_media_add_thumbnail_column' )) ) {
			add_filter('manage_upload_columns', [$this, 'media_add_thumbnail_column_define']);
			add_action('manage_media_custom_column', [$this, 'media_add_thumbnail_column_display'], 10, 2);
		}
		if( \Waboot\functions\get_option( $this->name . '_media_download_link' ) == true ) {
			add_action('admin_footer-upload.php', [$this, 'print_download_js'] );
			add_filter('media_row_actions', [$this, 'row_download_link'], 10, 3);
			add_action('admin_head-upload.php', [$this, 'download_button_css'] );
		}

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



	/*
	 * * ******************** *
	 * | GENERAL TWEAKS GROUP |
	 * * ******************** *
	 */

	/**
	 * @hooked: admin_footer_text
	 */
	public function general_footer_message() {
		echo \Waboot\functions\get_option($this->name.'_general_footer_message');
	}

	/**
	 * @hooked: admin_head
	 */
	public function general_extra_css() {
		$css = \Waboot\functions\get_option($this->name.'_general_extra_css');
		echo '<style type="text/css">'.$css.'</style>';
	}

	/**
	 * hoked: admin_init
	 * priority: 1
	 */
	public function hide_update_msg(){
		! current_user_can( 'install_plugins' )
		and remove_action( 'admin_notices', 'update_nag', 3 );
	}

	/**
	 * @hooked: admin_bar_menu
	 * priority: 999
	 */
	public function remove_wp_logo( $wp_admin_bar ) {
		$wp_admin_bar->remove_node( 'wp-logo' );
	}


	/**
	 * * ******************** *
	 * | LOGIN TWEAKS GROUP |
	 * * ******************** *
	 */

	/**
	 * @hooked: login_enqueue_scripts
	 */
	public function login_logo() {
		$url = (!empty(\Waboot\functions\get_option($this->name.'_login_logo_image')))
			? 'background-image: url('.\Waboot\functions\get_option($this->name.'_login_logo_image').');'
			. 'background-size: contain;'
		    . 'width: auto;'
			. 'padding-bottom: 30px;'
			: '';
		$height = (!empty(\Waboot\functions\get_option($this->name.'_login_logo_height')))
			? 'height: '
			. \Waboot\functions\get_option($this->name.'_login_logo_height')
			. 'px;'
			: '';
		echo '<style type="text/css">'
			. '#login h1 a, .login h1 a {'
				. $url
				. $height
			. '}'
		. '</style>';
	}

	/**
	 * @hooked: login_headerurl
	 */
	public function login_logo_url() {
		return \Waboot\functions\get_option($this->name.'_login_logo_url');
	}

	/**
	 * @hooked: login_headertitle
	 */
	function login_logo_title() {
		return \Waboot\functions\get_option($this->name.'_login_logo_title');
	}

	/**
	 * @hooked: login_head
	 */
	public function login_custom_css() {
		$css = \Waboot\functions\get_option($this->name.'_login_custom_css');
		echo '<style type="text/css">'.$css.'</style>';
	}

	/**
	 * @hooked: login_head
	 */
	public function login_background_image(){
		$url = \Waboot\functions\get_option($this->name.'_login_background_image');
		echo '<style type="text/css">
			body.login {
				background: url('.$url.') center no-repeat;
				background-size: cover;
			}
		</style>';
	}


	/*
	 * * ************************ *
	 * | DASHBOARD FUNCTION GROUP |
	 * * ************************ *
	 */

	/**
	 * @hooked: dashboard_glance_items
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

	/**
	 * @hooked: 'wp_dashboard_setup'
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

	/*
	 * Manipulates thumbnails attributes and properties in wp-admin/upload.php
	 * @hooked: 'wp_dashboard_setup'
	 */
	public function media_bigger_thumbs() {
		?>
		<script type="text/javascript" id="waboot-admin-tweaks-bigger-thumb">
			jQuery(document).ready( function($) {
				$(window).load(function(){
					$('table.media .image-icon img').each(function () {
						$(this)
							.removeAttr('width').css('max-width', '100%')
							.removeAttr('height').css('max-height', '100%')
							.removeAttr('srcset')
							.removeAttr('sizes');
					});
					$('.image-icon').css('width', '150px');
				})
			});
		</script>
		<?php
	}

	/**
	 * @hooked: 'image_size_names_choose'
	 */
	public function media_include_sizes($sizes) {
		global $_wp_additional_image_sizes;
		if( empty( $_wp_additional_image_sizes ) )
			return $sizes;

		foreach( $_wp_additional_image_sizes as $id => $data )
		{
			if( !isset( $sizes[$id] ) )
				$sizes[$id] = ucfirst( str_replace( '-', ' ', $id ) );
		}
		return $sizes;
	}

	/**
	 *
	 * @hooked: 'image_make_intermediate_size'
	 * @param $resized_file
	 * @return WP_Error
	 */
	public function media_sharpen_resized( $resized_file ) {

		$image = $this->my_wp_load_image( $resized_file );
		if( !is_resource( $image ) )
			return new WP_Error( 'error_loading_image', $image);

		$size = @getimagesize( $resized_file );
		if( !$size )
			return new WP_Error( 'invalid_image', __( 'Could not read image size' ));
		list($orig_w, $orig_h, $orig_type) = $size;

		switch( $orig_type )
		{
			case IMAGETYPE_JPEG:
				$matrix = array(
					array( -1, -1, -1 ),
					array( -1, 16, -1 ),
					array( -1, -1, -1 ),
				);

				$divisor = array_sum( array_map( 'array_sum', $matrix ) );
				$offset	 = 0;
				imageconvolution( $image, $matrix, $divisor, $offset );
				imagejpeg( $image, $resized_file, apply_filters( 'jpeg_quality', 90, 'edit_image' ) );
				break;
			case IMAGETYPE_PNG:
				return $resized_file;
			case IMAGETYPE_GIF:
				return $resized_file;
		}

		return $resized_file;
	}

	/*
	 * load image as {resource}
	 */
	public function my_wp_load_image($file) {

		if ( is_numeric( $file ) )
			$file = get_attached_file( $file );

		if ( ! is_file( $file ) )
			return sprintf(__('File &#8220;%s&#8221; doesn&#8217;t exist?'), $file);

		if ( ! function_exists('imagecreatefromstring') )
			return __('The GD image library is not installed.');

		// Set artificially high because GD uses uncompressed images in memory.
		wp_raise_memory_limit( 'image' );

		$image = imagecreatefromstring( file_get_contents( $file ) );

		if ( !is_resource( $image ) )
			return sprintf(__('File &#8220;%s&#8221; is not an image.'), $file);

		return $image;
	}


	/**
	 * Manage Meta Boxes removal
	 *
	 * @hooked: 'add_meta_boxes'
	 */
	public function media_remove_metaboxes(){
		$options = \Waboot\functions\get_option( $this->name . '_media_remove_metaboxes' );
		if($options['author']) {
			remove_meta_box( 'authordiv', 'attachment', 'normal' );
		}
		if($options['comments']) {
			remove_meta_box( 'commentsdiv', 'attachment', 'normal' );
		}
		if($options['slug']) {
			remove_meta_box( 'slugdiv', 'attachment', 'normal' );
		}
		if($options['discussion']) {
			remove_meta_box( 'commentstatusdiv', 'attachment', 'normal' );
		}
	}

	/*
	 * @hooked: 'manage_upload_columns'
	 */
	public function media_add_thumbnail_column_define($columns){
		$columns['all_thumbs'] = 'All Thumbs';
		return $columns;
	}

	/*
	 * @hooked: 'manage_media_custom_column'
	 */
	public function media_add_thumbnail_column_display($column_name, $post_id) {
		if( 'all_thumbs' != $column_name || !wp_attachment_is_image( $post_id ) )
			return;

		$full_size = wp_get_attachment_image_src( $post_id, 'full' );
		echo '<div style="clear:both">FULL SIZE : ' . $full_size[1] . ' x ' . $full_size[2] . '</div>';

		$size_names = get_intermediate_image_sizes();

		foreach( $size_names as $name ){
			// TODO: CHECK THIS: http://wordpress.org/support/topic/wp_get_attachment_image_src-problem
			$the_list = wp_get_attachment_image_src( $post_id, $name );

			if( $the_list[3] )
				echo '<div style="clear:both"><a href="' . $the_list[0] . '" target="_blank">' . $name . '</a> : ' . $the_list[1] . ' x ' . $the_list[2] . '</div>';
		}
	}


	/**
	 * Enqueue download script
	 *
	 * @hooked: 'admin_footer-upload.php'
	 */
	public function print_download_js(){ ?>
		<script>
			jQuery(document).ready( function($)
			{
				$('.mtt-downloader').click( function(e)
				{
					e.preventDefault();
					window.open($(this).attr('href'));
				});
			});
		</script>
	<?php }

	/**
	 * Add download link to row actions in wp-admin/upload.php
	 *
	 * @param array $actions
	 * @param $post
	 * @return string
	 * @hooked 'media_row_actions'
	 */
	public function row_download_link( $actions, $post ) {
		$actions['Download'] = '<a href="'
		                       . wp_get_attachment_url( $post->ID )
		                       . '" class="mtt-downloader" alt="Download link" title="'
		                       . __( 'Right click and choose Save As', 'mtt' )
		                       . '">Download</a>';

		return $actions;
	}

	/**
	 * Print custom columns CSS
	 * @hooked: 'admin_head-upload.php'
	 */
	public function download_button_css()
	{
		echo '<style type="text/css">.mtt-downloader{cursor:pointer}</style>' . "\r\n";
	}

	/**
	 * Allow svg update
	 *
	 * @param array $existing_mime_types
	 *
	 * @return array
	 *
	 * @hooked: 'upload_mimes'
	 */
	public function media_allow_svg_uploads( $existing_mime_types = array() ) {
		return $existing_mime_types + array( 'svg' => 'image/svg+xml' );
	}

	/**
	 * Consider this the "server side" fix for dimensions.
	 * Which is needed for the Media Grid within the Administratior.
	 *
	 * @see https://github.com/grok/wordpress-plugin-scalable-vector-graphics/blob/master/scalable-vector-graphics.php
	 *
	 * @param $response
	 * @param $attachment
	 * @param $meta
	 *
	 * @return mixed
	 *
	 * @hooked 'wp_prepare_attachment_for_js'
	 */
	public function media_allow_svg_adjust_response( $response, $attachment, $meta ) {
		if( $response['mime'] == 'image/svg+xml' && empty( $response['sizes'] ) ) {
			$svg_file_path = get_attached_file( $attachment->ID );
			$dimensions = WBF\components\utils\Utilities::get_svg_dimensions( $svg_file_path );
			$response[ 'sizes' ] = array(
				'full' => array(
					'url' => $response[ 'url' ],
					'width' => $dimensions->width,
					'height' => $dimensions->height,
					'orientation' => $dimensions->width > $dimensions->height ? 'landscape' : 'portrait'
				)
			);
		}
		return $response;
	}

	/**
	 * Consider this the "client side" fix for dimensions. But only for the Administrator.
	 *
	 * @see https://github.com/grok/wordpress-plugin-scalable-vector-graphics/blob/master/scalable-vector-graphics.php
	 * @hooked 'admin_enqueue_scripts'
	 */
	function media_allow_svg_administration_styles() {
		// Media Listing Fix
		wp_add_inline_style( 'wp-admin', ".media .media-icon img[src$='.svg'] { width: auto; height: auto; }" );
		// Featured Image Fix
		wp_add_inline_style( 'wp-admin', "#postimagediv .inside img[src$='.svg'] { width: 100%; height: auto; }" );
	}


	/**
	 * Consider this the "client side" fix for dimensions. But only for the End User
	 *
	 * @see https://github.com/grok/wordpress-plugin-scalable-vector-graphics/blob/master/scalable-vector-graphics.php
	 * @hooked 'wp_head'
	 */
	function media_allow_svg_public_styles() {
		// Featured Image Fix
		echo "<style>.post-thumbnail img[src$='.svg'] { width: 100%; height: auto; }</style>";
	}

}