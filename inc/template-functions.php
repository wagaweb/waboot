<?php

namespace Waboot\functions;
use Waboot\Layout;
use WBF\components\mvc\HTMLView;
use WBF\components\utils\Query;
use WBF\components\utils\Utilities;

/**
 * Wrapper for \WBF\modules\options\of_get_option
 *
 * @param $name
 * @param bool $default
 *
 * @return bool|mixed
 */
function get_option($name, $default = null){
	if(class_exists("WBF")){
		return \WBF\modules\options\of_get_option($name,$default);
	}else{
		return $default;
	}
}

/**
 * Wrapper for \WBF\modules\behaviors\get_behavior
 *
 * @param $name
 * @param $default
 * @param int $post_id
 * @param string $return
 *
 * @return array|bool|mixed|string
 */
function get_behavior($name, $default = "", $post_id = 0, $return = "value"){
	if(class_exists("WBF")){
		$result = \WBF\modules\behaviors\get_behavior($name, $post_id, $return);
		if($result === false || is_wp_error($result)){
			if(is_wp_error($result) && isset($result->error_data['unable_to_retrieve_behavior']) && isset($result->error_data['unable_to_retrieve_behavior']['default'])){
				$default = $result->error_data['unable_to_retrieve_behavior']['default'];
			}
			return $default;
		}
		return $result;
	}else{
		return $default;
	}
}

/**
 * Checks if at least one widget area with $prefix is active (eg: footer-1, footer-2, footer-3...)
 *
 * @param $prefix
 *
 * @return bool
 */
function count_widgets_in_area($prefix){
	$count = 0;
	$areas = get_widget_areas();
	if(isset($areas[$prefix]) || !isset($areas[$prefix]['type']) || $areas[$prefix]['type'] != "multiple"){
		$limit = isset($areas[$prefix]['subareas']) && intval($areas[$prefix]['subareas']) > 0 ? $areas[$prefix]['subareas'] : 0;
		for($i = 1; $i <= $limit; $i++) {
			if(is_active_sidebar($prefix . "-" . $i)) {
				$count++;
			}
		}
	}
	return $count;
}

/**
 * Prints out a waboot-type widget area
 *
 * @param $prefix
 */
function print_widgets_in_area($prefix){
	$count = count_widgets_in_area($prefix);
	if($count === 0) return;
	$sidebar_class = get_grid_class_for_alignment($count);
	(new HTMLView("templates/widget_areas/parts/multi-widget-area.php"))->clean()->display([
		'widget_area_prefix' => $prefix,
		'widget_count' => $count,
		'sidebar_class' => $sidebar_class
	]);
}

/**
 * Get the correct CSS class to align $count containers
 *
 * @param int $count
 *
 * @return string
 *
 */
function get_grid_class_for_alignment($count = 4){
	$class = '';
	$count = intval($count);
	switch($count) {
		case 1:
			$class = 'col-sm-12';
			break;
		case 2:
			$class = 'col-sm-6';
			break;
		case 3:
			$class = 'col-sm-4';
			break;
		case 4:
			$class = 'col-sm-3';
			break;
		default:
			$class = 'col-sm-1';
	}
	$class = apply_filters("waboot/layout/grid_class_for_alignment",$class,$count);
	return $class;
}

/**
 * Gets theme widget areas
 *
 * @return array
 */
function get_widget_areas(){
	$areas = [
		'header' => [
			'name' =>  __('Header', 'waboot'),
			'description' => __( 'The main widget area displayed in the header.', 'waboot' ),
			'render_zone' => 'header'
		],
		'main_top' => [
			'name' => __('Main Top', 'waboot'),
			'description' => __( 'Widget area displayed above the content and the sidebars.', 'waboot' ),
			'render_zone' => 'main-top'
		],
		'sidebar_primary' => [
			'name' => __('Sidebar primary', 'waboot'),
			'description' => __('Widget area displayed in left aside', 'waboot' ),
			'render_zone' => 'aside-primary'
		],
		'content_top' => [
			'name' => __('Content Top', 'waboot'),
			'description' => __('Widget area displayed above the content', 'waboot' ),
			'render_zone' => 'content',
			'render_priority' => 9
		],
		'content_bottom' => [
			'name' => __('Content Bottom', 'waboot'),
			'description' => __('Widget area displayed below the content', 'waboot' ),
			'render_zone' => 'content',
			'render_priority' => 90
		],
		'sidebar_secondary' => [
			'name' => __('Sidebar secondary', 'waboot'),
			'description' => __('Widget area displayed in right aside', 'waboot' ),
			'render_zone' => 'aside-secondary'
		],
		'main_bottom' => [
			'name' => __('Main Bottom', 'waboot'),
			'description' => __( 'Widget area displayed below the content and the sidebars.', 'waboot' ),
			'render_zone' => 'main-bottom'
		],
		'footer' => [
			'name' => __('Footer', 'waboot'),
			'description' => __( 'The main widget area displayed in the footer.', 'waboot' ),
			//'type' => 'multiple',
			//'subareas' => 4, //this will register footer-1, footer-2, footer-3 and footer-4 as widget areas
			'render_zone' => 'footer'
		]
	];

	$areas = apply_filters("waboot/widget_areas/available",$areas);

	return $areas;
}

/**
 * Returns the index page title
 *
 * @return string
 */
function get_index_page_title(){
	return single_post_title('', false);
}

/**
 * Returns the appropriate title for the archive page. Clone of get_the_archive_title() with some additions.
 *
 * @return string
 */
function get_archive_page_title(){
	if ( is_category() ) {
		/* translators: Category archive title. 1: Category name */
		$title = sprintf( __( 'Category: %s' ), single_cat_title( '', false ) );
	} elseif ( is_tag() ) {
		/* translators: Tag archive title. 1: Tag name */
		$title = sprintf( __( 'Tag: %s' ), single_tag_title( '', false ) );
	} elseif ( is_author() ) {
		/* translators: Author archive title. 1: Author name */
		$title = sprintf( __( 'Author: %s' ), '<span class="vcard">' . get_the_author() . '</span>' );
	} elseif ( is_year() ) {
		/* translators: Yearly archive title. 1: Year */
		$title = sprintf( __( 'Year: %s' ), get_the_date( _x( 'Y', 'yearly archives date format' ) ) );
	} elseif ( is_month() ) {
		/* translators: Monthly archive title. 1: Month name and year */
		$title = sprintf( __( 'Month: %s' ), get_the_date( _x( 'F Y', 'monthly archives date format' ) ) );
	} elseif ( is_day() ) {
		/* translators: Daily archive title. 1: Date */
		$title = sprintf( __( 'Day: %s' ), get_the_date( _x( 'F j, Y', 'daily archives date format' ) ) );
	} elseif ( is_tax( 'post_format' ) ) {
		if ( is_tax( 'post_format', 'post-format-aside' ) ) {
			$title = _x( 'Asides', 'post format archive title' );
		} elseif ( is_tax( 'post_format', 'post-format-gallery' ) ) {
			$title = _x( 'Galleries', 'post format archive title' );
		} elseif ( is_tax( 'post_format', 'post-format-image' ) ) {
			$title = _x( 'Images', 'post format archive title' );
		} elseif ( is_tax( 'post_format', 'post-format-video' ) ) {
			$title = _x( 'Videos', 'post format archive title' );
		} elseif ( is_tax( 'post_format', 'post-format-quote' ) ) {
			$title = _x( 'Quotes', 'post format archive title' );
		} elseif ( is_tax( 'post_format', 'post-format-link' ) ) {
			$title = _x( 'Links', 'post format archive title' );
		} elseif ( is_tax( 'post_format', 'post-format-status' ) ) {
			$title = _x( 'Statuses', 'post format archive title' );
		} elseif ( is_tax( 'post_format', 'post-format-audio' ) ) {
			$title = _x( 'Audio', 'post format archive title' );
		} elseif ( is_tax( 'post_format', 'post-format-chat' ) ) {
			$title = _x( 'Chats', 'post format archive title' );
		}
	} elseif ( is_post_type_archive() ) {
		/* translators: Post type archive title. 1: Post type name */
		$title = sprintf( __( 'Archives: %s' ), post_type_archive_title( '', false ) );
	} elseif ( is_tax() ) {
		$tax = get_taxonomy( get_queried_object()->taxonomy );
		/* translators: Taxonomy term archive title. 1: Taxonomy singular name, 2: Current taxonomy term */
		$title = sprintf( __( '%1$s: %2$s' ), $tax->labels->singular_name, single_term_title( '', false ) );
	} else {
		$arch_obj = get_queried_object();
		if(isset($arch_obj->name)){
			$title = $arch_obj->name;
		}else{
			$title = __('Archives', 'waboot');
		}
	}

	/**
	 * Filters the archive title.
	 *
	 * @since 4.1.0
	 *
	 * @param string $title Archive title to be displayed.
	 */
	return apply_filters( 'get_the_archive_title', $title );
}

/**
 * Handles the different theme options values that can be set for archives pages. If the $taxonomy is 'category', then
 * the Blog options are used, otherwise the function looks for the option specific to that $taxonomy.
 * Look options.php at "Archives" section for more info.
 *
 * @param string $provided_option_name (without suffix, so for: 'blog_display_title', 'display_title' is enough )
 * @param string|false $taxonomy
 *
 * @return string
 */
function get_archive_option($provided_option_name,$taxonomy = null){
	if(!isset($taxonomy)){
		$taxonomy = get_current_taxonomy();
	}

	if(!$taxonomy && is_archive()){
		global $wp_query;
		$taxonomy = isset($wp_query->query['post_type']) ? $wp_query->query['post_type'] : false;
	}

	$taxonomy = apply_filters('waboot/archive_option/taxonomy',$taxonomy);

	$default_value = \Waboot\functions\get_option("blog_".$provided_option_name); //Default to blog values

	if($taxonomy === "category" || !$taxonomy){
		$option_name = "blog_".$provided_option_name;
	}else{
		$option_name = "archive_".$taxonomy."_".$provided_option_name;
	}

	$value = \Waboot\functions\get_option($option_name,$default_value);

	return $value;
}

/**
 * Gets the body layout
 *
 * @return string
 */
function get_body_layout(){
	static $layout;
	if(!isset($layout)){
		$current_page_type = Utilities::get_current_page_type();
		if($current_page_type == Utilities::PAGE_TYPE_BLOG_PAGE || $current_page_type == Utilities::PAGE_TYPE_DEFAULT_HOME || is_category()) {
			$layout = \Waboot\functions\get_option('blog_layout');
		}elseif(is_archive()){
			$layout = get_archive_option('layout');
		}
		else{
			$layout = \Waboot\functions\get_behavior('layout');
		}
		$layout = apply_filters("waboot/layout/body_layout",$layout);
	}
	return $layout;
}

/**
 * Checks if body layout is full width
 *
 * @return bool
 */
function body_layout_is_full_width(){
	$body_layout = get_body_layout();
	return $body_layout == Layout::LAYOUT_FULL_WIDTH;
}

/**
 * Checks if the body layout has two sidebars
 * 
 * @return bool
 */
function body_layout_has_two_sidebars(){
	$body_layout = get_body_layout();
	return in_array($body_layout,array("two-sidebars","two-sidebars-right","two-sidebars-left"));
}

/**
 * Checks if the body layout features at least one sidebar
 *
 * @use body_layout_has_two_sidebars()
 *
 * @return bool
 */
function body_layout_has_sidebar(){
	$body_layout = get_body_layout();
	return $body_layout == Layout::LAYOUT_PRIMARY_LEFT || $body_layout == Layout::LAYOUT_PRIMARY_RIGHT || body_layout_has_two_sidebars();
}

/**
 * Return the class relative to the $blog_layout (by default the current blog layout)
 *
 * @param bool $blog_layout
 * @return mixed
 */
function get_posts_wrapper_class(){
	$classes = [
		"blog-classic"
	];
	$classes = apply_filters("waboot/layout/posts_wrapper/class",$classes);
	return implode(" ",$classes);
}

/**
 * Get the specified sidebar size
 * @param $name ("primary" or "secondary")
 *
 * @return string|false
 */
function get_sidebar_size($name){
	$size = false;

	$page_type = Utilities::get_current_page_type();

	if($page_type == Utilities::PAGE_TYPE_BLOG_PAGE || $page_type == Utilities::PAGE_TYPE_DEFAULT_HOME || is_category()){
		$size = of_get_option("blog_".$name."_sidebar_size");
	}elseif(is_archive() || is_tax()){
		$size = get_archive_option($name."_sidebar_size");
	}else{
		$size = get_behavior($name.'-sidebar-size');
	}

	return $size;
}

/**
 * Returns the sizes of each column available into current layout
 * @return array of integers
 */
function get_cols_sizes(){
	$result = array("main"=>12);
	if (\Waboot\functions\body_layout_has_two_sidebars()) {
		//Primary size
		$primary_sidebar_width = get_sidebar_size("primary");
		if(!$primary_sidebar_width) $primary_sidebar_width = 0;
		//Secondary size
		$secondary_sidebar_width = get_sidebar_size("secondary");
		if(!$secondary_sidebar_width) $secondary_sidebar_width = 0;
		//Main size
		$mainwrap_size = 12 - Layout::layout_width_to_int($primary_sidebar_width) - Layout::layout_width_to_int($secondary_sidebar_width);

		$result = [
			"main" => $mainwrap_size,
			"primary" => Layout::layout_width_to_int($primary_sidebar_width),
			"secondary" => Layout::layout_width_to_int($secondary_sidebar_width)
		];
	}else{
		if(\Waboot\functions\get_body_layout() != Layout::LAYOUT_FULL_WIDTH){
			$primary_sidebar_width = get_sidebar_size("primary");
			if(!$primary_sidebar_width) $primary_sidebar_width = 0;
			$mainwrap_size = 12 - Layout::layout_width_to_int($primary_sidebar_width);

			$result = [
				"main" => $mainwrap_size,
				"primary" => Layout::layout_width_to_int($primary_sidebar_width)
			];
		}
	}
	$result = apply_filters("waboot/layout/get_cols_sizes",$result);
	return $result;
}

/**
 * Filterable version of get_template_part
 *
 * @param $slug
 * @param null $name
 */
function get_template_part($slug,$name = null){
	$page_type = Utilities::get_current_page_type();
	$tpl_part = apply_filters("waboot/layout/template_parts",[$slug,$name],$page_type);
	\get_template_part($tpl_part[0],$tpl_part[1]);
}

/**
 * Save theme options favicon as WordPress favicon
 *
 * @global \wpdb $wpdb
 * @global \WP_Site_Icon $wp_site_icon
 */
function deploy_favicon($option, $old_value, $value){
	if(!isset($value['favicon']) || $value['favicon'] == "") return;
	global $wpdb,$wp_site_icon;
	//Retrieve the attachment
	$attachment_id = call_user_func(function() use($value){
		global $wpdb;
		$attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $value['favicon'] ));
		if(isset($attachment[0])) return absint($attachment[0]);
		else return false;
	});
	if(!$attachment_id) return;

	//The code below is a slightly adapted version of: wp_ajax_crop_image() for 'site-icon'
	if(!isset($wp_site_icon)){
		$wp_site_icon = new \WP_Site_Icon();
	}

	$attachment_metadata = wp_get_attachment_metadata($attachment_id);
	$context = "site-icon";
	$cropDetails = [
		'x1' => 0,
		'y1' => 0,
		'x2' => $attachment_metadata['width'],
		'y2' => $attachment_metadata['height'],
		'width' => $attachment_metadata['width'],
		'height' => $attachment_metadata['height'],
		'dst_width' => 512,
		'dst_height' => 512
	];
	$data = array_map( 'absint', $cropDetails );

	//Waboot S3-Compatibility HACK
	add_filter("waboot-s3-manager/get_attached_file/must_download",function($must_download,$file,$attachment_id){
		return true; //This will make wp_crop_image to download the file on get_attached_file()
	}, 10, 3);
	//---|

	$src = \WBF\components\utils\Paths::url_to_path(wp_get_attachment_image_url($attachment_id));
	$cropped = wp_crop_image( $src, 0, 0, $data['width'], $data['height'], $data['dst_width'], $data['dst_height'] );

	if ( ! $cropped || is_wp_error( $cropped ) ) return;

	/** This filter is documented in wp-admin/custom-header.php */
	$cropped = apply_filters( 'wp_create_file_in_uploads', $cropped, $attachment_id ); // For replication.
	$object  = $wp_site_icon->create_attachment_object( $cropped, $attachment_id );
	unset( $object['ID'] );

	// Update the attachment.
	add_filter( 'intermediate_image_sizes_advanced', array( $wp_site_icon, 'additional_sizes' ) );

	//Waboot S3-Compatibility HACK
	do_action("waboot-s3-manager/clear_attachment",$attachment_id);
	add_action("waboot-s3-manager/before_remove_base_image_from_local_fs", function($pathname,$data,$attachment_id,$module){
		$r = $module->plugin->upload_file($pathname);
	}, 10, 4);
	//---|

	$attachment_id = $wp_site_icon->insert_attachment( $object, $cropped );

	remove_filter( 'intermediate_image_sizes_advanced', array( $wp_site_icon, 'additional_sizes' ) );

	// Additional sizes in wp_prepare_attachment_for_js().
	add_filter( 'image_size_names_choose', array( $wp_site_icon, 'additional_sizes' ) );

	//Update the theme option
	//$value['favicon'] = $object->guid; //no we can't here...

	//Update the option
	$wpdb->update($wpdb->options,['option_value' => $attachment_id],['option_name' => 'site_icon']);
}

/**
 * Print out the custom css file from the theme options value. Called during "update_option".
 *
 * It's a callback set in options "save action" param in options.php
 *
 * @param $option
 * @param $old_value
 * @param $value
 *
 * @return FALSE|string
 */
function deploy_theme_options_css($option, $old_value, $value){
	$input_file_path = apply_filters("waboot/assets/theme_options_style_file/source", get_template_directory()."/assets/src/css/_theme-options.src");
	$output_file_path = apply_filters("waboot/assets/theme_options_style_file/destination", WBF()->get_working_directory()."/theme-options.css");

	if(!is_array($value)) return false;

	$output_string = "";

	$tmpFile = new \SplFileInfo($input_file_path);
	if((!$tmpFile->isFile() || !$tmpFile->isWritable())){
		return false;
	}

	$parsedFile = $output_file_path ? new \SplFileInfo($output_file_path) : null;
	if(!is_dir($parsedFile->getPath())){
		mkdir($parsedFile->getPath());
	}

	$genericOptionfindRegExp = "/{{ ?([a-zA-Z0-9\-_]+) ?}}/";
	$funcRegExp = "/{{ ?apply:([a-zA-Z\-_]+)\(([a-zA-Z0-9\-_, ]+)\) ?}}/";
	$fontOptionfindRegExp = "/\{\{ ?font: ?([a-z]+) ?\}\}/";
	$assignOptionFindRegExp = "/\{\{ ?font-assignment: ?([a-z]+) ?\}\}/";

	$tmpFileObj = $tmpFile->openFile( "r" );
	$parsedFileObj = $parsedFile->openFile( "w" );
	$byte_written = 0;

	while(!$tmpFileObj->eof()){
		$line = $tmpFileObj->fgets();
		//Replace {{ <theme-option-id> }}
		if(preg_match($genericOptionfindRegExp, $line, $matches)){
			if(array_key_exists( $matches[1], $value)){
				if($value[ $matches[1] ] != ""){
					$line = preg_replace( $genericOptionfindRegExp, $value[$matches[1]], $line);
				}else{
					$line = "\t/*{$matches[1]} is empty*/\n";
				}
			}else{
				$line = "\t/*{$matches[1]} not found*/\n";
			}
		}

		//Replace {{ apply:<func>(<theme-option-id>) }}
		if(preg_match($funcRegExp, $line, $matches)){
			require_once get_template_directory()."/inc/styles-functions.php";
			if(count($matches) == 3 && function_exists($matches[1])){
				$func = $matches[1];
				$args = explode(",",$matches[2]);
				foreach ($args as $k => $v){
					if(isset($value[$v])){
						$args[$k] = $value[$v]; //If one of the args is a theme option name, replace it with it's value!
					}
				}
				if(function_exists($func)){
					$r = call_user_func($func,$args);
					$line = preg_replace( $funcRegExp, $r, $line);
				}else{
					$line = "\t/*$func not found*/\n";
				}
			}else{
				$line = "\t/*Invalid function call*/\n";
			}
		}

		//Replace {{ font: <theme-option-id> }}
		if(preg_match($fontOptionfindRegExp, $line, $matches)){
			if(array_key_exists( $matches[1], $value) && $value[ $matches[1] ] != "" && isset($value[ $matches[1] ]['import'])){
				$fonts = $value[ $matches[1] ]['import'];
				$families = '';
				$subsets = '';
				$arr_subsets = [];

				if (is_array($fonts) && count($fonts) > 0) {
					$i = 0;
					foreach ( $fonts as $font ) {
						$families_separator = ($i>0) ? '|' : '';

						if (count($font['weight']) == 1){
							if ($font['weight'][0] == 'regular') {
								$weight = "";
							} else {
								$weight = ":" . implode(",", $font['weight']);
							}
						} else if (count($font['weight'])>1) {
							$weight = ":".implode(",", $font['weight']);
							$weight = str_replace ( "regular" , "400" , $weight);
						} else {
							$weight = "";
						}

						$font['family'] = preg_replace("/[\s]/", "+", $font['family']);
						$families .= $families_separator . $font['family'] . $weight;

						// builds an array with all the subsets of all the selected fonts
						foreach ( $font['subset'] as $subset ) {
							if ($subset != 'latin') {       // latin is excluded
								array_push($arr_subsets, $subset);
							}
						}
						$i++;
					}

					$arr_subsets = array_unique($arr_subsets);
					if (count($arr_subsets) > 0) {          // if we have some subset different from latin
						// another loop for array of subsets
						$j = 0;
						foreach ( $arr_subsets as $subset ) {
							$subsets_start = ($j==0) ? '&subset=' : '';
							$subsets_separator = ($j>0) ? ',' : '';
							$subsets .= $subsets_start . $subsets_separator . $subset; // e.g. &subset=greek,latin-ext';

							$j++;
						}
					}

					$css_rule = "@import 'https://fonts.googleapis.com/css?family=".$families."".$subsets."';";
					$line = preg_replace( $fontOptionfindRegExp, $css_rule, $line);
				} else {
					$line = "\t/*{$matches[1]} no fonts assigned*/\n";
				}
			}else{
				$line = "\t/*{$matches[1]} not found or invalid*/\n";
			}
		}

		//Replace {{ font-assignment: <theme-option-id> }}
		if(preg_match($assignOptionFindRegExp, $line, $matches)){
			if(array_key_exists( $matches[1], $value) &&  $value[ $matches[1] ] != "" && isset($value[ $matches[1] ]['assign'])){
				$assignments = $value[ $matches[1] ]['assign'];
				$css_rule = "";
				foreach($assignments as $selector => $props){

					if($props['weight'] == "regular") {
						$props['weight'] = "400";
					} elseif (preg_match('/italic/', $props['weight'])) {
						$props['weight'] = preg_replace('/italic/', '', $props['weight']);
						if ($props['weight'] == '') $props['weight'] = '400';
						$props['style'] = 'italic';
					}
					$selector = preg_replace('/-/',',', $selector);

					$css_rule .= "{$selector}{\n";
					$css_rule .= "\tfont-family: '{$props['family']}';\n";
					$css_rule .= "\tfont-weight: {$props['weight']};\n";
					if ($props['style'] != '') $css_rule .= "\tfont-style: {$props['style']};\n";
					$css_rule .= "}\n";
				}
				$line = preg_replace( $assignOptionFindRegExp, $css_rule, $line);
			}else{
				$line = "\t/*{$matches[1]} not found*/\n";
			}
		}

		$byte_written += $parsedFileObj->fwrite($line);
	}

	return $output_string;
}

/**
 * Get the start wizard link
 *
 * @return string
 */
function get_start_wizard_link(){
	if(wbf_exists()){
		$start_wizard_link = admin_url("admin.php?page=waboot_setup_wizard");
	}else{
		$start_wizard_link = admin_url("tools.php?page=waboot_setup_wizard");
	}
	return $start_wizard_link;
}

/**
 * Return current taxonomy name
 *
 * @return bool|string
 */
function get_current_taxonomy(){
	$o = get_queried_object();
	if($o instanceof \WP_Term){
		return $o->taxonomy;
	}elseif($o instanceof \WP_Taxonomy){
		return $o->name;
	}
	return false;
}

/**
 * Detect the existence of WBF
 *
 * @return bool
 */
function wbf_exists(){
	if(class_exists("\WBF\PluginCore") || defined('WBTEST_CURRENT_PATH')){
		return true;
	}
	return false;
}

/**
 * Check if WBF is at least at the required version
 */
function has_wbf_required_version($required_version){
	if(!wbf_exists()) return false;
	$wbf = WBF();
	$wbf_version = $wbf::version;
	$r = version_compare($wbf_version,$required_version,'>=');
	return $r;
}

/**
 * Gets Waboot installed children themes
 * @return array
 */
function get_waboot_children(){
	$themes = wp_get_themes();
	$children = [];
	if(\is_array($themes) && !empty($themes)){
		foreach ($themes as $theme_slug => $WP_Theme){
			if($WP_Theme->get_template() === 'waboot'){
				$children[] = $WP_Theme;
			}
		}
	}
	return $children;
}

/**
 * Backup theme options of a specific theme
 *
 * @param string WP_Theme|string $theme
 * @param string|null $filename
 *
 * @return bool|string
 * @throws \Exception
 */
function backup_theme_options($theme, $filename = null){
	if(\is_string($theme)){
		$theme = wp_get_theme($theme);
	}
	if(!$theme instanceof \WP_Theme) return false;
	$theme_options = \get_option("wbf_".$theme->get_stylesheet()."_options",[]);
	if(!\is_array($theme_options) || empty($theme_options)) return false;

	//Actually backup theme options
	$backup_path = WBF()->get_working_directory(true) . "/{$theme->get_stylesheet()}/theme-options-backups";
	if(!is_dir($backup_path)){
		wp_mkdir_p($backup_path);
	}
	if(is_dir($backup_path)){
		if(!isset($filename) || !\is_string($filename)){
			$date = date( 'Y-m-d-His' );
			$backup_filename = 'wbf_'.$theme->get_stylesheet() . "_options-" . $date . ".options";
		}else{
			$backup_filename = $filename;
			if(strpos($backup_filename,'.options') === false){
				$backup_filename.= '.options';
			}
		}

		if(is_file($backup_path . "/" . $backup_filename)){
			unlink($backup_path . "/" . $backup_filename);
		}

		if ( ! file_put_contents( $backup_path . "/" . $backup_filename, base64_encode( json_encode( $theme_options ) ) ) ) {
			throw new \Exception( __( "Unable to create the backup file: " . $backup_path . "/" . $backup_filename ) );
		}
		return $backup_path . "/" . $backup_filename;
	}
	return false;
}

/**
 * Backups components states of a specific theme
 *
 * @param string $theme
 *
 * @param null $filename
 *
 * @return string|bool
 * @throws \Exception
 */
function backup_components_states($theme, $filename = null){
	if(\is_string($theme)){
		$theme = wp_get_theme($theme);
	}
	if(!$theme instanceof \WP_Theme) return false;

	$states = \call_user_func(function() use($theme){
		$opt = \get_option("wbf_".$theme->get_stylesheet()."_components_state", []);
		$opt = apply_filters("wbf/modules/components/states",$opt,$theme->get_stylesheet());
		return $opt;
	});

	//Actually backup components options
	$backup_path = WBF()->get_working_directory(true) . "/{$theme->get_stylesheet()}/components-backups";
	if(!is_dir($backup_path)){
		wp_mkdir_p($backup_path);
	}
	if(is_dir($backup_path)){
		if(!isset($filename) || !\is_string($filename)){
			$date = date( 'Y-m-d-His' );
			$backup_filename = $theme->get_stylesheet() . "-" . $date . ".components";
		}else{
			$backup_filename = $filename;
			if(strpos($backup_filename,'.components') === false){
				$backup_filename.= '.components';
			}
		}

		if(is_file($backup_path . "/" . $backup_filename)){
			unlink($backup_path . "/" . $backup_filename);
		}

		if ( ! file_put_contents( $backup_path . "/" . $backup_filename, serialize( $states ) ) ) {
			throw new \Exception( __( "Unable to create the backup file: " . $backup_path . "/" . $backup_filename ) );
		}
		return $backup_path . "/" . $backup_filename;
	}
	return false;
}