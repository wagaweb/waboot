<?php

namespace Waboot\hooks;
use function Waboot\functions\backup_components_states;
use function Waboot\functions\backup_theme_options;
use function Waboot\functions\get_waboot_children;
use Waboot\LS;
use WBF\components\customupdater\Theme_Update_Checker;
use WBF\components\license\License_Manager;
use WBF\modules\components\Component;
use WBF\modules\options\Framework;
use function WBF\modules\update_channels\get_update_channel;

/**
 * Add header metas
 */
function add_header_metas(){
	get_template_part("templates/parts/meta");
}
add_action("waboot/head/start",__NAMESPACE__."\\add_header_metas");

/**
 * Adds Waboot credits
 *
 * @param $text
 *
 * @return mixed
 */
function add_credits($text){
    $text = preg_replace('/.<\/span>$/','',$text);
	$our_text = '</span>'.sprintf(__(" and <a href='%s'>Waboot</a>.","waboot"),"https://www.waboot.io");
	$text.=$our_text;
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
    if(\defined('WABOOT_NO_UPDATE') && WABOOT_NO_UPDATE) return;
    $no_update = get_option('WABOOT_NO_UPDATE',false);
    if($no_update) return;

	$slug = "waboot";

	$channel = get_update_channel('waboot_theme');
	if(!$channel || $channel === 'stable'){
	    $metadata_call = "http://update.waboot.org/resource/info/theme/waboot";
    }else{
		$metadata_call = "http://update.waboot.org/resource/info/theme/waboot?channel=".$channel;
	}

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

/**
 * Allows to bind actions before Waboot updates
 *
 * @param $reply
 * @param $package
 * @param \WP_Upgrader $WP_Upgrader
 *
 * @return mixed
 */
function on_before_update($reply, $package, $WP_Upgrader){
	if(!$WP_Upgrader instanceof  \Theme_Upgrader) return $reply;
	if(strpos( $package, 'waboot' ) === false) return $reply;

	//Detect update infos
	$theme = wp_get_theme('waboot');
	$current_version = $theme['Version'];
	$new_version = \call_user_func(function() use($package){
		preg_match('/\/-?([0-9.]+)/',$package,$matches);
		if(\is_array($matches) && !empty($matches)){
			return $matches[1];
		}
		return false;
	});
	if(!$new_version) return $reply;

	$infos = [
	    'parent_theme' => $theme,
        'current_theme' => wp_get_theme(),
	    'current_version' => $current_version,
        'new_version' => $new_version,
        'package' => $package
    ];

	do_action('waboot/before_update', $infos, $WP_Upgrader);

    return $reply;
}
add_filter('upgrader_pre_download', __NAMESPACE__."\\on_before_update",99,3);

/**
 * Save the current Waboot version before the actual update
 *
 * @param $params
 * @param $WP_Upgrader
 */
function save_waboot_version_before_update($params, $WP_Upgrader){
    $version = isset($params['current_version']) ? $params['current_version'] : wp_get_theme('waboot')['Version'];
    update_option('waboot_pre_upgrade_version', $version);
}
add_action('waboot/before_update', __NAMESPACE__."\\save_waboot_version_before_update",10,2);

/**
 * Create a theme options backup before an update
 *
 * @param $params
 * @param \WP_Upgrader $WP_Upgrader
 *
 * @return void
 */
function do_backups_before_updates($params, $WP_Upgrader){
    try{
        $themes_to_backup = get_waboot_children();
        if(!\is_array($themes_to_backup) || empty($themes_to_backup)) return;

	    //Do theme options backups
	    $waboot_updates = \get_option('waboot_updates_backups_theme_options',[]);
        foreach ($themes_to_backup as $theme){
            if(!$theme instanceof \WP_Theme) continue;
            try{
	            $filename = backup_theme_options($theme);
	            $hash = $params['current_version'].'_'.$params['new_version'].'_'.$theme->get_stylesheet();
	            $waboot_updates[$hash] = [
		            'from' => $params['current_version'],
		            'to' => $params['new_version'],
		            'file' => $filename,
		            'theme' => $theme->get_stylesheet()
	            ];
            }catch(\Exception $e){
                continue;
            }
        }
	    \update_option('waboot_updates_backups_theme_options',$waboot_updates);

        //Do components backup
	    $waboot_updates = \get_option('waboot_updates_backups_components',[]);
	    foreach ($themes_to_backup as $theme){
		    if(!$theme instanceof \WP_Theme) continue;
		    try{
			    $filename = backup_components_states($theme);
			    $hash = $params['current_version'].'_'.$params['new_version'].'_'.$theme->get_stylesheet();
			    $waboot_updates[$hash] = [
				    'from' => $params['current_version'],
				    'to' => $params['new_version'],
				    'file' => $filename,
				    'theme' => $theme->get_stylesheet()
			    ];
            }catch (\Exception $e){
		        continue;
            }
	    }
	    \update_option('waboot_updates_backups_components',$waboot_updates);

    }catch(\Exception $e){}
}
add_action('waboot/before_update', __NAMESPACE__."\\do_backups_before_updates",10,2);