<?php

namespace Waboot\hooks;
use Waboot\LS;
use WBF\components\customupdater\Theme_Update_Checker;
use WBF\components\license\License_Manager;
use WBF\modules\components\Component;
use WBF\modules\components\ComponentsManager;
use WBF\modules\options\Framework;

/**
 * Add header metas
 */
function add_header_metas(){
	get_template_part("templates/parts/meta");
}
add_action("waboot/head/start",__NAMESPACE__."\\add_header_metas");

/**
 * Adds apple touch init to the document head meta
 */
function add_apple_touch_icon(){
	?>
	<link rel="apple-touch-icon" href="<?php apply_filters("waboot/assets/apple-touch-icon-path","apple-touch-icon.png"); ?>">
	<?php
}
add_action("waboot/head/meta",__NAMESPACE__."\\add_apple_touch_icon");

/**
 * Adds banner sidebar zone to header
 */
function add_banner_wrapper(){
	if(!is_active_sidebar('banner')) return;
	get_template_part("templates/parts/banner-wrapper");
}
add_action("waboot/header",__NAMESPACE__."\\add_banner_wrapper");

/**
 * Adds Waboot credits
 *
 * @param $text
 *
 * @return mixed
 */
function add_credits($text){
	$our_text = sprintf(__(", and <a href='%s'>Waboot</a>","waboot"),""); //todo: finire
	return $text;
}
add_filter("admin_footer_text",__NAMESPACE__."\\add_credits");

/**
 * Sets the default components
 *
 * @param $default_components
 *
 * @return array
 */
function set_default_components($default_components){
	$default_components = [
		'header_classic',
		'footer_classic',
		'breadcrumb',
		'topNavWrapper',
	];
	return $default_components;
}
add_filter("wbf/modules/components/defaults",__NAMESPACE__."\\set_default_components");

/**
 * Puts some custom post types into blacklist (in these post types the behavior will never be displayed)
 * @param $blacklist
 * @return array
 */
function behaviors_cpts_blacklist($blacklist){
	$blacklist[] = "metaslider";
	return $blacklist;
}
add_filter("wbf/modules/behaviors/post_type_blacklist",__NAMESPACE__."\\behaviors_cpts_blacklist");

/**
 * Ignore sticky posts in archives
 * @param \WP_Query $query
 */
function ignore_sticky_post_in_archives($query){
	if(is_category() || is_tag() || is_tax()) {
		$query->set("post__not_in",get_option( 'sticky_posts', array() ));
	}
}
add_action( 'pre_get_posts', __NAMESPACE__.'\\ignore_sticky_post_in_archives' );

/**
 * Manage Waboot Update Server
 */
function set_update_server(){
	$slug = "waboot";
	$metadata_call = "http://update.waboot.org/resource/info/theme/waboot";
	$tup = new Theme_Update_Checker($slug,$metadata_call);
}
add_action("wbf_init",__NAMESPACE__."\\set_update_server");

/**
 * Adds the Waboot Update channels
 *
 * @param $channels
 *
 * @return mixed
 */
function set_update_channels($channels){
    $channels['waboot'] = [
        'name' => 'Waboot Theme',
        'slug' => 'waboot_theme',
        'channels' => [
            'Stable' => 'stable',
            'Beta' => 'beta'
        ]
    ];
    return $channels;
}
add_filter('wbf/update_channels/available',__NAMESPACE__.'\\set_update_channels');

/**
 * Injects Waboot custom templates
 *
 * @param array $page_templates
 * @param \WP_Theme $theme
 * @param \WP_Theme|null $post
 *
 * @return array
 */
function inject_templates($page_templates, \WP_Theme $theme, $post){
	$template_directory = get_stylesheet_directory()."/templates/parts-tpl";
	$template_directory = apply_filters("waboot/custom_template_parts_directory",$template_directory);
	$tpls = glob($template_directory."/content-*.php");
	foreach ($tpls as $tpl){
		$basename = basename($tpl);
		$name = call_user_func(function() use ($basename) {
			preg_match("/^content-([a-z_-]+)/",$basename,$matches);
			if(isset($matches[1])){
				$name = $matches[1];
			}
			if(isset($name)) return $name; else return false;
		});
		if(!$name) continue;
		$page_templates[$name] = str_replace("_"," ",ucfirst($name))." "._x("(parts)","Waboot Template Partials","waboot");
	}
	return $page_templates;
}
add_filter("theme_page_templates",__NAMESPACE__."\\inject_templates", 999, 3);

/**
 * Automatically set the "_enabled_for_all_pages" value accordingly to load_locations and load_locations_ids when components options are saved or components status are changed
 *
 * @param $options
 * @param $registered_components
 *
 * @return mixed
 */
function automatically_set_enabled_status_for_components($options,$registered_components){
    if(!is_array($registered_components)) return $options;
	foreach($registered_components as $name => $data){
		if(isset($options[$name."_load_locations_ids"])){
			$load_locations_by_ids = $options[$name."_load_locations_ids"];
			$load_locations = isset($options[$name."_load_locations"]) ? $options[$name."_load_locations"] : [];
			$load_locations = array_filter($load_locations); //remove FALSE elements
			if($load_locations_by_ids == "" && empty($load_locations)){
				$options[$name."_enabled_for_all_pages"] = "on";
			}else{
				$options[$name."_enabled_for_all_pages"] = "off";
			}
		}elseif(isset($_POST['components_status']) && array_key_exists($name,$_POST['components_status']) && $_POST['components_status'][$name] == "on"){
			$saved_options = Framework::get_saved_options();
			$load_locations_by_ids = isset($saved_options[$name."_load_locations_ids"]) ? $saved_options[$name."_load_locations_ids"] : "";
			$load_locations = isset($saved_options[$name."_load_locations"]) ? $saved_options[$name."_load_locations"] : [];
			$load_locations = array_filter($load_locations); //remove FALSE elements
			if($load_locations_by_ids == "" && empty($load_locations)){
				$options[$name."_enabled_for_all_pages"] = "on";
			}else{
				$options[$name."_enabled_for_all_pages"] = "off";
			}
        }
	}
	return $options;
}
add_filter("wbf/modules/components/options_sanitization_before_save",__NAMESPACE__."\\automatically_set_enabled_status_for_components", 10, 2);

/**
 * Automatically set the "_enabled_for_all_pages" value accordingly to load_locations and load_locations_ids when a components is activated
 *
 * @param Component $component
 */
function automatically_set_enabled_status_for_component_on_activate(Component $component){
	//Update "Enabled on all pages"
    $options = Framework::get_saved_options();
    if(is_array($options) && array_key_exists($component->name."_enabled_for_all_pages",$options)){
	    $load_locations_by_ids = isset($options[$component->name."_load_locations_ids"]) ? $options[$component->name."_load_locations_ids"] : "";
	    $load_locations = isset($options[$component->name."_load_locations"]) ? $options[$component->name."_load_locations"] : [];
	    $load_locations = array_filter($load_locations); //remove FALSE elements
	    if($load_locations_by_ids == "" && empty($load_locations)){
		    $options_to_update[$component->name."_enabled_for_all_pages"] = "1";
	    }else{
		    $options_to_update[$component->name."_enabled_for_all_pages"] = false;
	    }

	    $options_to_update = wp_parse_args($options_to_update,$options);

	    $r = update_option(Framework::get_options_root_id(),$options_to_update);
    }else{
        //todo: check if the following behavior has meaning
	    /*$r = update_option(Framework::get_options_root_id(),[
		    $component->name."_enabled_for_all_pages" => 1
        ]);*/
    }
}
add_action("wbf/modules/components/on_activate", __NAMESPACE__."\\automatically_set_enabled_status_for_component_on_activate");

/**
 * Sort components categories
 */
function sort_components_categories($categories){
    if(array_key_exists('Layout',$categories)){
        $categories['Layout'] = 0;
    }
    if(array_key_exists('Effects',$categories)){
        $categories['Effects'] = 1;
    }
    if(array_key_exists('Utilities',$categories)){
        $categories['Utilities'] = 2;
    }
    return $categories;
}
add_filter("wbf/modules/components/categories_weights", __NAMESPACE__."\\sort_components_categories");

/**
 * Adds custom classes to components default options fields
 *
 * @param $options
 * @param $component
 *
 * @return mixed
 */
function add_classes_to_component_default_options($options,$component){
    foreach ($options as $k => $v){
        if(isset($options[$k]['class'])){
            $options[$k]['class'] = $options[$k]['class'] . " full_option";
        }else{
	        $options[$k]['class'] = "full_option";
        }
    }
    return $options;
}
add_filter("wbf/modules/components/component/default_options",__NAMESPACE__ ."\\add_classes_to_component_default_options",10,2);