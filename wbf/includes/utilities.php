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
			if ( file_exists( STYLESHEETPATH . '/' . $template_name ) ) {
				$located = STYLESHEETPATH . '/' . $template_name;
				break;
			} elseif ( file_exists( TEMPLATEPATH . '/' . $template_name ) ) {
				$located = TEMPLATEPATH . '/' . $template_name;
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