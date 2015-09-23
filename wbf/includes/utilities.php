<?php

if(!function_exists('wbf_get_template_part')):
	function wbf_get_template_part($slug, $name = null){
		do_action( "get_template_part_{$slug}", $slug, $name );

		$templates = apply_filters("wbf/get_template_part/path:{$slug}",array(),array($slug,$name)); //@deprecated from WBF ^0.11.0
		$name = (string) $name;
		if ( '' !== $name )
			$templates['names'][] = "{$slug}-{$name}.php";

		$templates['names'][] = "{$slug}.php";

		wbf_locate_template($templates, true, false);
	}
endif;

if(!function_exists('wbf_locate_template')):
	function wbf_locate_template($templates, $load = false, $require_once = true ) {
		$located = '';
		$template_names = $templates['names'];
		$template_sources = isset($templates['sources']) ? $templates['sources'] : array();
		$registered_base_paths = apply_filters("wbf/get_template_part/base_paths",array());

		//Search into template dir
		foreach ( (array) $template_names as $template_name ) {
			if ( ! $template_name ) {
				continue;
			}
			if ( file_exists( get_stylesheet_directory() . '/' . $template_name ) ) {
				$located = get_stylesheet_directory() . '/' . $template_name;
				break;
			} elseif ( file_exists( get_template_directory() . '/' . $template_name ) ) {
				$located = get_template_directory() . '/' . $template_name;
				break;
			} elseif(!empty($registered_base_paths)){
				//Search into registered base dirs
				foreach($registered_base_paths as $path){
					$path = rtrim($path,"/") . '/'.ltrim($template_name,"/");
					if(file_exists( $path )){
						$located = $path;
						break;
					}
				}
				if($located){
					break;
				}
			}
		}

		//Search into plugins dir
		if(empty($located)) {
			foreach($template_sources as $template_name){
				if ( !$template_name )
					continue;
				if( file_exists($template_name)){
					$located = $template_name;
					break;
				}
			}
		}

		if ( $load && '' != $located )
			load_template( $located, $require_once );

		return $located;
	}
endif;

if (!function_exists( 'wbf_locate_template_uri' )):
    /**
     * Retrieve the URI of the highest priority template file that exists.
     *
     * Searches in the stylesheet directory before the template directory so themes
     * which inherit from a parent theme can just override one file.
     *
     * @param string|array $template_names Template file(s) to search for, in order.
     * @return string The URI of the file if one is located.
     */
    function wbf_locate_template_uri($template_names){
        $located = '';
        foreach ((array)$template_names as $template_name) {
            if (!$template_name)
                continue;

            if (file_exists(get_stylesheet_directory() . '/' . $template_name)) {
                $located = get_stylesheet_directory_uri() . '/' . $template_name;
                break;
            } else if (file_exists(get_template_directory() . '/' . $template_name)) {
                $located = get_template_directory_uri() . '/' . $template_name;
                break;
            }
        }

        return $located;
    }
endif;

if (!function_exists( "wbf_get_filtered_post_types" )):
	/**
	 * Get a list of post types without the blacklisted ones
	 * @param array $blacklist
	 *
	 * @return array
	 */
	function wbf_get_filtered_post_types($blacklist = array()){
		$post_types = get_post_types();
		$result = array();
		$blacklist = array_unique(array_merge($blacklist,array('attachment','revision','nav_menu_item','ml-slider','acf-field-group','acf-field')));
		foreach($post_types as $pt){
			if(!in_array($pt,$blacklist)){
				$pt_obj = get_post_type_object($pt);
				$result[$pt_obj->name] = $pt_obj->label;
			}
		}

		return $result;
	}
endif;

/**
 * Get posts while preserving memory
 *
 * @param callable $callback a function that will be called for each post. You can use it to additionally filter the posts. If it returns true, the post will be added to output array.
 * @param array    $args normal arguments for WP_Query
 *
 * @return array of posts
 */
function wbf_get_posts(\closure $callback = null, $args = array()){
	$all_posts = [];
	$page = 1;
	$get_posts = function ( $args ) use ( &$page ) {
		$args = wp_parse_args( $args, array(
			'post_type' => 'post',
			'paged' => $page,
		) );
		$all_posts = new \WP_Query( $args );
		if ( count( $all_posts->posts ) > 0 ) {
			return $all_posts;
		} else {
			return false;
		}
	};
	while ( $paged_posts = $get_posts( $args ) ) {
		$i = 0;
		while ( $i <= count( $paged_posts->posts ) - 1 ) { //while($all_posts->have_posts()) WE CANNOT USE have_posts... too many issue
			//if($i == 1) $all_posts->next_post(); //The first next post does not change $all_posts->post for some reason... so we need to do it double...
			$p = $paged_posts->posts[ $i ];
			if(isset($callback)){
				$result = call_user_func( $callback, $p );
				if($result){
					$all_posts[$p->ID] = $p;
				}
			}else{
				$all_posts[$p->ID] = $p;
			}
			//if($i < count($all_posts->posts)) $all_posts->next_post();
			$i ++;
		}
		$page ++;
	}
	return $all_posts;
}

if (!function_exists( "wbf_admin_show_message" )) :
    function wbf_admin_show_message($m, $type) {
	    wbf_add_admin_notice("adm_notice_".rand(1,50),$m,$type,$args = ['category'=>'_flash_']);
    }
endif;

if (!function_exists("wbf_add_admin_notice")) :
	/**
	 * Add an admin notice
	 *
	 * @uses WBF\admin\Notice_Manager
	 *
	 * @param String $id
	 * @param String $message
	 * @param String $level (can be: "updated","error","nag")
	 * @param array $args (category[default:base], condition[default:null], cond_args[default:null])
	 */
	function wbf_add_admin_notice($id,$message,$level,$args = []){
		global $wbf_notice_manager;

		if(!isset($wbf_notice_manager)) return;

		$args = wp_parse_args($args,[
			"category" => 'base',
			"condition" => null,
			"cond_args" => null
		]);

		$wbf_notice_manager->add_notice($id,$message,$level,$args['category'],$args['condition'],$args['cond_args']);
	}
endif;

/***************************************************************
 * MOBILE DETECT FUNCTIONS
 ***************************************************************/

if (!function_exists("wb_is_mobile")):
    function wb_is_mobile()
    {
        $md = WBF::get_mobile_detect();
        return ($md->isMobile());
    }
endif;

if (!function_exists("wb_is_tablet")):
    function wb_is_tablet()
    {
        $md = WBF::get_mobile_detect();
        return ($md->isTablet());
    }
endif;

if (!function_exists("wb_is_ios")):
    function wb_is_ios()
    {
        $md = WBF::get_mobile_detect();
        return ($md->isiOS());
    }
endif;

if (!function_exists("wb_is_android")):
    function wb_is_android()
    {
        $md = WBF::get_mobile_detect();
        return ($md->isAndroidOS());
    }
endif;

if (!function_exists("wb_is_windows_mobile")):
    function wb_is_windows_mobile()
    {
        $md = WBF::get_mobile_detect();
        return ($md->is('WindowsMobileOS') || $md->is('WindowsPhoneOS'));
    }
endif;

if (!function_exists("wb_is_iphone")):
    function wb_is_iphone()
    {
        $md = WBF::get_mobile_detect();
        return ($md->isIphone());
    }
endif;

if (!function_exists("wb_is_ipad")):
    function is_ipad()
    {
        $md = WBF::get_mobile_detect();
        return ($md->isIpad());
    }
endif;

if (!function_exists("wb_is_samsung")):
    function wb_is_samsung()
    {
        $md = WBF::get_mobile_detect();
        return ($md->is('Samsung'));
    }
endif;

if (!function_exists("wb_is_samsung_tablet")):
    function wb_is_samsung_tablet()
    {
        $md = WBF::get_mobile_detect();
        return ($md->is('SamsungTablet'));
    }
endif;

if (!function_exists("wb_is_kindle")):
    function wb_is_kindle()
    {
        $md = WBF::get_mobile_detect();
        return ($md->is('Kindle'));
    }
endif;

if (!function_exists("wb_android_version")):
    function wb_android_version()
    {
        $md = WBF::get_mobile_detect();
        return $md->version('Android');
    }
endif;

if (!function_exists("wb_iphone_version")):
    function wb_iphone_version()
    {
        $md = WBF::get_mobile_detect();
        return $md->version('iPhone');
    }
endif;

if (!function_exists("wb_ipad_version")):
    function wb_ipad_version()
    {
        $md = WBF::get_mobile_detect();
        return $md->version('iPad');
    }
endif;

/**************************************************************
 * OTHERS
 **************************************************************/

if ( !function_exists("get_post_thumbnail_src") ) :
	function get_post_thumbnail_src($post_id,$size=null){
		$post_thumbnail_id = get_post_thumbnail_id($post_id);
		$thumbnail = wp_get_attachment_image_src($post_thumbnail_id,$size);
		return $thumbnail[0];
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

if ( !function_exists("recursive_array_search") ) :
	function recursive_array_search($needle,$haystack) {
		foreach($haystack as $key=>$value) {
			$current_key=$key;
			if($needle===$value OR (is_array($value) && recursive_array_search($needle,$value) !== false)) {
				return $current_key;
			}
		}
		return false;
	}
endif;

if ( !function_exists("remote_file_size") ) :
	/**
	 * Get Remote File Size
	 *
	 * @param string $url as remote file URL
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

if ( !function_exists("listFolderFiles") ) :
	function listFolderFiles($dir,$extension = "php"){
		$files_in_root = glob($dir."/*.{$extension}");
		$files = glob($dir."/*/*.{$extension}");

		if(!$files_in_root) $files_in_root = array();
		if(!$files) $files = array();

		return array_merge($files_in_root,$files);
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

if ( !function_exists("url_to_path") ) :
	function url_to_path($url){
		$blogurl = get_bloginfo("url");
		$blogurl = preg_replace("(https?://)", "", $blogurl );
		//$result = preg_match("/^https?:\/\/$blogurl\/([[:space:]a-zA-Z0-9\/_.-]+)/", $url, $matches);
		$result = preg_replace("|^https?://$blogurl|", ABSPATH, $url);
		//$blogpath = ABSPATH;

		//$filepath = $blogpath."/".$matches[1];
		//return $filepath;
		return $result;
	}
endif;

if ( !function_exists("path_to_url") ) :
	function path_to_url($path){
		$blogurl = trailingslashit(get_bloginfo("url"));
		$blogpath = ABSPATH;
		$result = preg_replace("|^$blogpath|", $blogurl, $path);
		return $result;
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
	/**
	 * Returns the offset from the origin timezone to the remote timezone, in seconds.
	 * @param $remote_tz;
	 * @param $origin_tz; If null the servers current timezone is used as the origin.
	 * @return int;
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

/***************************************************************
 * TYPOGRAPHY (these functions are deprecated)
 ***************************************************************/

/**
 * Returns an array of system fonts
 * Feel free to edit this, update the font fallbacks, etc.
 * @deprecated
 */
function options_typography_get_os_fonts() {
    // OS Font Defaults
    $os_faces = array(
        'Arial, sans-serif' => 'Arial',
        '"Avant Garde", sans-serif' => 'Avant Garde',
        'Cambria, Georgia, serif' => 'Cambria',
        'Copse, sans-serif' => 'Copse',
        'Garamond, "Hoefler Text", Times New Roman, Times, serif' => 'Garamond',
        'Georgia, serif' => 'Georgia',
        '"Helvetica Neue", Helvetica, sans-serif' => 'Helvetica Neue',
        'Tahoma, Geneva, sans-serif' => 'Tahoma'
    );
    return $os_faces;
}

/**
 * Returns a select list of Google fonts
 * Feel free to edit this, update the fallbacks, etc.
 * @deprecated
 */
function options_typography_get_google_fonts() {
    // Google Font Defaults
    $google_faces = array(
        '' => 'Select',
        'Abril Fatface, serif' => 'Abril Fatface',
        'Actor, sans-serif' => 'Actor',
        'Amaranth, sans-serif' => 'Amaranth',
        'Arvo, serif' => 'Arvo',
        'Average, sans-serif' => 'Average',
        'Bevan, serif' => 'Bevan',
        'Copse, sans-serif' => 'Copse',
        'Crimson Text, serif' => 'Crimson Text',
        'Dancing Script, cursive' => 'Dancing Script',
        'Droid Sans, sans-serif' => 'Droid Sans',
        'Droid Serif, serif' => 'Droid Serif',
        'EB Garamond, serif' => 'EB Garamond',
        'Exo, sans-serif' => 'Exo',
        'Exo 2, sans-serif' => 'Exo 2',
        'Fjord, serif' => 'Fjord',
        'Forum, serif' => 'Forum',
        'Gentium Basic, serif' => 'Gentium Basic',
        'Gravitas One, serif' => 'Gravitas One',
        'Istok Web, sans-serif' => 'Istok Web',
        'Italiana, serif' => 'Italiana',
        'Josefin Slab, sans-serif' => 'Josefin Slab',
        'Jura, sans-serif' => 'Jura',
        'Kreon, serif' => 'Kreon',
        'Lato, sans-serif' => 'Lato',
        'Ledger Regular, sans-serif' => 'Ledger Regular',
        'Lobster, cursive' => 'Lobster',
        'Montserrat, sans-serif' => 'Montserrat',
        'Nobile, sans-serif' => 'Nobile',
        'Old Standard TT, serif' => 'Old Standard TT',
        'Open Sans, sans-serif' => 'Open Sans',
        'Oswald, sans-serif' => 'Oswald',
        'Pacifico, cursive' => 'Pacifico',
        'Raleway, sans-serif' => 'Raleway',
        'Rokkitt, serif' => 'Rokkit',
        'Playfair Display, serif' => 'Playfair Display',
        'Poly, serif' => 'Poly',
        'PT Sans, sans-serif' => 'PT Sans',
        'PT Serif, serif' => 'PT Serif',
        'Quattrocento, serif' => 'Quattrocento',
        'Raleway, cursive' => 'Raleway',
        'Roboto, sans-serif' => 'Roboto',
        'Roboto Condensed, sans-serif' => 'Roboto Condensed',
        'Roboto Slab, serif' => 'Roboto Slab',
        'Signika, sans-serif' => 'Signika',
        'Stalemate, cursive' => 'Stalemate',
        'Source Sans Pro, sans-serif' => 'Source Sans Pro',
        'Ubuntu, sans-serif' => 'Ubuntu',
        'Vollkorn, serif' => 'Vollkorn',
        'Yanone Kaffeesatz, sans-serif' => 'Yanone Kaffeesatz'
    );
    return $google_faces;
}