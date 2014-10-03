<?php

if ( !function_exists("images_url") ) :
	function images_url(){
		echo get_images_url();
	}
endif;

if ( !function_exists("get_images_url") ) :
	function get_images_url(){
		return get_bloginfo("template_url")."/assets/images";
	}
endif;

if ( !function_exists("the_slug") ) :
	function the_slug(){
		echo get_the_slug();
	}
endif;

if ( !function_exists("get_the_slug") ) :
	function get_the_slug(){
		global $post;
		$post_data = get_post($post->ID,ARRAY_A);
		$slug  = $post_data['post_name'];
		return $slug;
	}
endif;

if ( !function_exists("get_post_thumbnail_src") ) :
	function get_post_thumbnail_src($post_id,$size=null){
		$post_thumbnail_id = get_post_thumbnail_id($post_id);
		$thumbnail = wp_get_attachment_image_src($post_thumbnail_id,$size);
		return $thumbnail[0];
	}
endif;

if(!function_exists("is_plugin_active")) :
	function is_plugin_active($plugin){
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		return is_plugin_active($plugin);
	}
endif;

if ( !function_exists("get_current_url") ) :
	function get_current_url() {
		$pageURL = 'http';
		if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}
endif;

if ( !function_exists("get_wp_current_url") ) :
	function wp_get_current_url(){
		global $wp;
		$current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
		return $current_url;
	}
endif;

if (!function_exists("wp_get_filtered_post_types")):
	function wp_get_filtered_post_types($blacklist = array()){
		$post_types = get_post_types();
		$result = array();
		$blacklist = array_unique(array_merge($blacklist,array('attachment','revision','nav_menu_item','ml-slider')));
		foreach($post_types as $pt){
			if(!in_array($pt,$blacklist)){
				$pt_obj = get_post_type_object($pt);
				$result[$pt_obj->name] = $pt_obj->label;
			}
		}

		return $result;
	}
endif;

if ( !function_exists("array_neighbor") ) :
	/**
	 * Get the next and prev element in an array relative to the current
	 * @param $arr of items
	 * @param $key of current item
	 * @return array
	 */
	function array_neighbor($arr, $key)
	{
		$keys = array_keys($arr);
		$keyIndexes = array_flip($keys);

		$return = array();
		if (isset($keys[$keyIndexes[$key]-1])) {
			$return[] = $keys[$keyIndexes[$key]-1];
		}
		else {
			$return[] = $keys[sizeof($keys)-1];
		}

		if (isset($keys[$keyIndexes[$key]+1])) {
			$return[] = $keys[$keyIndexes[$key]+1];
		}
		else {
			$return[] = $keys[0];
		}

		return $return;
	}
endif;

if ( !function_exists("remote_file_size") ) :
	/**
	 * Get Remote File Size
	 *
	 * @param sting $url as remote file URL
	 * @return int as file size in byte
	 */
	function remote_file_size($url){
		# Get all header information
		$data = get_headers($url, true);
		# Look up validity
		if (isset($data['Content-Length']))
			# Return file size
			return (int) $data['Content-Length'];
	}
endif;

if ( !function_exists("formatBytes") ) :
	/**
	 * Converts bytes into human readable file size.
	 *
	 * @param string $bytes
	 * @param int $precision
	 * @return string human readable file size (2,87 МB)
	 */
	function formatBytes($bytes, $precision = 2) {
		$units = array('B', 'KB', 'MB', 'GB', 'TB');

		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);

		// Uncomment one of the following alternatives
		$bytes /= pow(1024, $pow);
		// $bytes /= (1 << (10 * $pow));

		return round($bytes, $precision) . ' ' . $units[$pow];
	}
endif;

if ( !function_exists("createdir") ) :
	function createdir($path){
		if(!is_dir($path)){
			if(!mkdir($path,0777)){
				throw new WPCriticalErr(_("Unable to create folder {$path}"));
				return false;
			}else{
				return true;
			}
		}
	}
endif;

if ( !function_exists("deltree") ) :
	/**
	 * Completely erase a directory
	 * @param $dir the directory path
	 */
	function deltree($dir){
		if(!preg_match("|[A-Za-z0-9]+/$|",$dir)) $dir .= "/"; // ensure $dir ends with a slash

		$files = glob( $dir . '*', GLOB_MARK );
		foreach($files as $file){
			if( substr( $file, -1 ) == '/' )
				deltree( $file );
			else
				unlink( $file );
		}
		if(is_dir($dir)) rmdir( $dir );
	}
endif;

if ( !function_exists("count_digit") ) :
	function count_digit($number){
		$digit = 0;
		do
		{
			$number /= 10;      //$number = $number / 10;
			$number = intval($number);
			$digit++;
		}while($number!=0);
		return $digit;
	}
endif;

if ( !function_exists("get_timezone_offset") ) :
	/**    Returns the offset from the origin timezone to the remote timezone, in seconds.
	 *    @param $remote_tz;
	 *    @param $origin_tz; If null the servers current timezone is used as the origin.
	 *    @return int;
	 */
	function get_timezone_offset($remote_tz, $origin_tz = null) {
	    if($origin_tz === null) {
	        if(!is_string($origin_tz = date_default_timezone_get())) {
	            return false; // A UTC timestamp was returned -- bail out!
	        }
	    }
	    $origin_dtz = new DateTimeZone($origin_tz);
	    $remote_dtz = new DateTimeZone($remote_tz);
	    $origin_dt = new DateTime("now", $origin_dtz);
	    $remote_dt = new DateTime("now", $remote_dtz);
	    $offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);
	    return $offset;
	}
endif;

/** New Custom-metaboxes */
if(class_exists("cmb_Meta_Box")) :
	/**
	 * Text Numbers
	 */
	add_action( 'cmb_render_text_number', 'sm_cmb_render_text_number', 10, 2 );
	function sm_cmb_render_text_number( $field, $meta ) {
		echo '<input class="cmb_text_small" type="number" name="', $field['id'], '" id="', $field['id'], '" value="', '' !== $meta ? $meta : $field['std'], '" /><span class="cmb_metabox_description">', $field['desc'], '</span>';
	}
	// validate the field
	add_filter( 'cmb_validate_text_number', 'sm_cmb_validate_text_number' );
	function sm_cmb_validate_text_number( $new ) {
		$new = preg_replace("/[^0-9]/","",$new);

		return $new;
	}
	/**
	 * Post-Select
	 * For the times when you need to relate one post to another this little bastard comes in handy.
	 */
	// render post select
	add_action( 'cmb_render_post_select', 'sm_cmb_render_post_select', 10, 2 );
	function sm_cmb_render_post_select( $field, $meta ) {
		$post_type = ($field['post_type'] ? $field['post_type'] : 'post');
		$limit = ($field['limit'] ? $field['limit'] : '-1');
		echo '<select name="', $field['id'], '" id="', $field['id'], '">';
		$posts = get_posts('post_type='.$post_type.'&numberposts='.$limit.'&posts_per_page='.$limit);

		foreach ( $posts as $art ) {
			if ($art->ID == $meta ) {
				echo '<option value="' . $art->ID . '" selected>' . get_the_title($art->ID) . '</option>';
			} else {
				echo '<option value="' . $art->ID . '  ">' . get_the_title($art->ID) . '</option>';
			}
		}
		echo '</select>';
		echo '<p class="cmb_metabox_description">', $field['desc'], '</p>';
	}
	// the field doesnt really need any validation, but just in case
	add_filter( 'cmb_validate_post_select', 'rrh_cmb_validate_post_select' );
	function rrh_cmb_validate_post_select( $new ) {
		return $new;
	}

	/**
	 * Separator
	 */
	add_action( 'cmb_render_separator', 'lp_cmb_render_separator', 10, 2 );
	function lp_cmb_render_separator($field,$meta){
		?>
		<p class="cmd_separator" style="font-size: 1.5em; margin-bottom: 0;"><strong><?php echo $field['name'] ?></strong></p>
	<?php
	}
endif;