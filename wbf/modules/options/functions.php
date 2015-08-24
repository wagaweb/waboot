<?php

namespace WBF\modules\options;
use \WBF\modules\components\ComponentsManager;


/**
 * Checks if the dependencies of theme options are met
 */
function of_check_options_deps(){
    global $wbf_notice_manager;
    $deps_to_achieve = _of_get_theme_options_deps();
    if(!empty($deps_to_achieve)){
        if(!empty($deps_to_achieve['components'])){
            $wbf_notice_manager->clear_notices("theme_opt_component_deps_everyrun");
            foreach($deps_to_achieve['components'] as $c_name){
                if(!ComponentsManager::is_active($c_name)){
                    //Register new notice that tells that the component is not present
                    $message = __("An option requires the component <strong>$c_name</strong>, but it is not active.","wbf");
                    $wbf_notice_manager->add_notice($c_name."_not_active",$message,"error","theme_opt_component_deps_everyrun");
                }else{
                    $wbf_notice_manager->remove_notice($c_name."_not_active");
                }
            }
        }else{
            $wbf_notice_manager->clear_notices("theme_opt_component_deps_everyrun");
        }
    }else{
        $wbf_notice_manager->clear_notices("theme_opt_component_deps_everyrun");
    }
}

/**
 * Performs actions during Theme Option saving (called during "update_option")
 *
 * @param $option
 * @param $old_value
 * @param $value
 *
 * @uses of_generate_less_file()
 * @throws \Exception
 */
function of_options_save($option, $old_value, $value){
    global $wbf_notice_manager;
    $config = get_option( 'optionsframework' );
    if($option == $config['id']){
        $must_recompile_flag = false;
        $deps_to_achieve = array();
        $all_options = Framework::get_registered_options();

        /*
         * Check differences beetween new values and old value
         */
        $multidimensional_options = array();
        foreach($all_options as $k => $opt){
            if(isset($opt['std']) && is_array($opt['std'])){
                $multidimensional_options[$opt['id']] = $opt;
            }
        }
        $diff = @array_diff_assoc($old_value,$value);
        foreach($multidimensional_options as $id => $opt){
            if(isset($old_value[$id]) && isset($value[$id])){
                $tdiff = @array_diff_assoc($old_value[$id],$value[$id]);
                if(is_array($tdiff) && !empty($tdiff)){
                    $diff[$id] = $tdiff;
                }
            }
        }

        //Doing actions with modified options
        foreach($all_options as $k => $opt_data){
            if(isset($opt_data['id']) && array_key_exists($opt_data['id'],$diff)){ //True if the current option has been modified
	            /** BEGIN OPERATIONS HERE: **/
                /*
                 * Check upload fields
                 */
	            if($opt_data['type'] == "upload"){
		            $upload_to = isset($opt_data['upload_to']) ? $opt_data['upload_to'] : false;
		            $upload_as = isset($opt_data['upload_as']) ? $opt_data['upload_as'] : false;
		            $allowed_extensions = isset($opt_data['allowed_extensions']) ? $opt_data['allowed_extensions'] : array("jpg","jpeg","png","gif","ico");
		            $file_path = url_to_path($value[$opt_data['id']]);
					if(is_file($file_path)){ //by doing this we take into account only the files uploaded to the site and not external one
						$oFile = new \SplFileObject($file_path);
						try{
							if(!in_array($oFile->getExtension(),$allowed_extensions)) throw new \Exception("Invalid file extension");
							if($upload_to){
								//We need to copy the uploaded file and update the value
								if(is_dir($upload_to)){
									$upload_to = rtrim($upload_to,"/");
									$new_path = $upload_as && !empty($upload_as) ? $upload_to."/".$upload_as.".".$oFile->getExtension() : $upload_to."/".$oFile->getBasename();
									if(!copy($oFile->getRealPath(),$new_path)){
										throw new \Exception("Cant move file");
									}
									$new_opt_value = path_to_url($new_path);
									$value[$opt_data['id']] = $new_opt_value;
									Framework::set_option_value($opt_data['id'],$new_opt_value); //set new value
								}else{
									throw new \Exception("Invalid upload location");
								}
							}
						}catch(\Exception $e){
							//Reset the old value
							$old_opt_value = $old_value[$opt_data['id']];
							$value[$opt_data['id']] = $old_opt_value;
							Framework::set_option_value($opt_data['id'],$old_opt_value);
						}
					}
	            }
                /*
                 * Check if must recompile
                 */
                if(isset($opt_data['recompile_styles']) && $opt_data['recompile_styles']){
                    $must_recompile_flag = true;
                }
                /*
                 * Check theme options dependencies
                 */
                if(isset($opt_data['deps'])){
                    if(isset($opt_data['deps']['_global'])){
                        if(isset($opt_data['deps']['_global']['components']))
                            $deps_to_achieve['components'][] = $opt_data['deps']['_global']['components'];
                    }
                    unset($opt_data['deps']['_global']);
                    foreach($opt_data['deps'] as $v => $deps){
                        if(array_key_exists($opt_data['id'],$value) && $value[$opt_data['id']] == $v){ //true the option has the value specified into deps array
                            //Then set the deps to achieve
                            if(isset($deps['components'])) $deps_to_achieve['components'] = $deps['components'];
                        }
                    }
                }
            }
        }

	    /**
	     * If the "Reset to defaults" button was pressed
	     */
	    if(isset($_POST['reset'])){
		    $must_recompile_flag = true;
	    }

        if($must_recompile_flag){
	        of_recompile_styles($value);
        }

        if(!empty($deps_to_achieve)){
            $wbf_notice_manager->clear_notices("theme_opt_component_deps");
            if(!empty($deps_to_achieve['components'])){
                //Try to enable all the required components
                $registered_components = ComponentsManager::getAllComponents();
                foreach($deps_to_achieve['components'] as $c_name){
                    if(!ComponentsManager::is_active($c_name)){
                        if(ComponentsManager::is_present($c_name)){
                            ComponentsManager::enable($c_name, ComponentsManager::is_child_component( $c_name ));
                        }else{
                            //Register new notice that tells that the component is not present
                            $message = __("An option requires the component <strong>$c_name</strong>, but it is not present","wbf");
                            $wbf_notice_manager->add_notice($c_name."_component_not_present",$message,"error","theme_opt_component_deps","FileIsPresent",ComponentsManager::generate_component_mainfile_path($c_name));
                        }
                    }
                }
            }
        }else{
            $wbf_notice_manager->clear_notices("theme_opt_component_deps");
        }
    }
}

/**
 * Generate a new _theme-options-generated.less and recompile the styles
 * @param $values
 */
function of_recompile_styles($values){
	of_generate_less_file($values); //Create a _theme-options-generated.less file
	//Then, compile less
	if(isset($GLOBALS['wbf_styles_compiler']) && $GLOBALS['wbf_styles_compiler']){
		global $wbf_styles_compiler;
		$wbf_styles_compiler->compile();
	}
}

/**
 * Replace {of_get_option} and {of_get_font} tags in _theme-options-generated.less.cmp; It is called during "update_option" via of_options_save() and during "wbf/compiler/pre_compile" via hook
 * @param $value values of the options
 */
function of_generate_less_file($value = null){
	if(!isset($value) || empty($value)) $value = Framework::get_options_values();

	if(!is_array($value)) return;

    $tmpFile = new \SplFileInfo(get_stylesheet_directory()."/sources/less/_theme-options-generated.less.cmp");
    if(!$tmpFile->isFile() || !$tmpFile->isWritable()){
        $tmpFile = new \SplFileInfo(get_template_directory()."/sources/less/_theme-options-generated.less.cmp");
    }
    $parsedFile = new \SplFileInfo(get_stylesheet_directory()."/sources/less/theme-options-generated.less");
    if($tmpFile->isFile() && $tmpFile->isWritable()) {
        $genericOptionfindRegExp = "~//{of_get_option\('([a-zA-Z0-9\-_]+)'\)}~";
        $fontOptionfindRegExp    = "~//{of_get_font\('([a-zA-Z0-9\-_]+)'\)}~";

        $tmpFileObj    = $tmpFile->openFile( "r" );
        $parsedFileObj = $parsedFile->openFile( "w" );
	    $byte_written = 0;

        while ( ! $tmpFileObj->eof() ) {
            $line = $tmpFileObj->fgets();
            //Replace a generic of option
            if ( preg_match( $genericOptionfindRegExp, $line, $matches ) ) {
                if ( array_key_exists( $matches[1], $value ) ) {
                    if ( $value[ $matches[1] ] != "" ) {
                        $line = preg_replace( $genericOptionfindRegExp, $value[ $matches[1] ], $line );
                    } else {
                        $line = "//{$matches[1]} is empty\n";
                    }
                } else {
                    $line = "//{$matches[1]} not found\n";
                }
            }
            //Replace a font option
            if ( preg_match( $fontOptionfindRegExp, $line, $matches ) ) {
                $line = "//{$matches[1]} is empty\n";
                if ( array_key_exists( $matches[1], $value ) ) {
                    if ( $value[ $matches[1] ] != "" ) {
                        $attr       = $value[ $matches[1] ];
	                    if(isset($attr['category']))
                            $fontString = "font-family: '" . $attr['family'] . "', " . $attr['category'] . ";";
	                    else
		                    $fontString = "font-family: '" . $attr['family'] . "';";
                        /*if(preg_match("/([0-9]+)([a-z]+)/",$attr['style'],$style_matches)){
                            if($style_matches[1] == 'regular') $style_matches[1] = "normal";
                            $fontString .= "font-weight: ".$style_matches[1].";";
                            $fontString .= "font-style: ".$style_matches[2].";";
                        }else{
                            if($attr['style'] == 'regular') $attr['style'] = "normal";
                            $fontString .= "font-weight: ".$attr['style'].";";
                        }*/
                        $fontString .= "color: " . $attr['color'] . ";";
                        $line = $fontString;
                    } else {
                        $line = "//{$matches[1]} is empty\n";
                    }
                } else {
                    $line = "//{$matches[1]} not found\n";
                }
            }
	        $byte_written += $parsedFileObj->fwrite( $line );
        }
	    //Here the file has been written!
    }
}

/**
 * Returns an array with the dependencies of theme options
 * @param null $all_options
 * @return array
 */
function _of_get_theme_options_deps($all_options = null){
    $deps_to_achieve = array();
    if(!isset($all_options)) $all_options = Framework::get_registered_options();
    foreach($all_options as $k => $opt_data){
        if(isset($opt_data['id'])){
            $current_opt_name = $opt_data['id'];
            $current_value = of_get_option($current_opt_name);
            if(isset($opt_data['deps'])){
                if(isset($opt_data['deps']['_global'])){
                    if(isset($opt_data['deps']['_global']['components']))
                        $deps_to_achieve['components'][] = $opt_data['deps']['_global']['components'];
                }
                unset($opt_data['deps']['_global']);
                foreach($opt_data['deps'] as $v => $deps){
                    if($current_value == $v){ //true the option has the value specified into deps array
                        //Then set the deps to achieve
                        if(isset($deps['components'])) $deps_to_achieve['components'] = $deps['components'];
                    }
                }
            }
        }
    }
    return $deps_to_achieve;
}

/**
 * Check if current admin page is the options framework page
 * @param $hook
 * @return bool
 */
function of_is_admin_framework_page($hook){
    $menu = Admin::menu_settings();
    if ( $hook == 'waboot_page_' . $menu['old_menu_slug'] || $hook == 'toplevel_page_' . $menu['menu_slug']) {
        return true;
    }
    return false;
}

/**
 * Takes an array of options and returns the values themselves and the default value
 * @usage
 *
 * A typical array should be like this:
 *
 * array(
 *       array(
 *           "name" => __("Full width. No sidebar.","waboot"),
 *           "value" => "full-width"
 *       ),
 *       array(
 *           "name" => __("Sidebar right","waboot"),
 *           "value" => "sidebar-right"
 *       ),
 *       array(
 *           "name" => __("Sidebar left","waboot"),
 *           "value" => "sidebar-left"
 *       ),
 *       '_default' => 'sidebar-right'
 * )
 *
 * OR (more general):
 *
 * array(
 *       'opt1'
 *       'opt2,
 *       'opt2,
 *       '_default' => 'opt1'
 * )
 *
 * IF '_default' is not set or does not exists in the array, the function returns the first value (ore the 'value' field of the first key)
 *
 * @param $values
 * @return array
 */
function of_add_default_key($values){
    $default = false;

    if(isset($values['_default'])){
        if(array_key_exists($values['_default'],$values)){
            $default = $values['_default'];
        }else{
            foreach($values as $v){
                if(is_array($v)){
                    if($v['value'] == $values['_default']){
                        $default = $values['_default'];
                    }
                }
            }
        }
    }
    if(!isset($values['_default']) || $default == false){
        reset($values);
        $default = key($values);
        if(is_array($values[$default])){
            $default = $values[$default]['value'];
        }
    }
    if(isset($values['_default'])) unset($values['_default']);

    return array(
      'values' => $values,
      'default' => $default
    );
}

/*
 * IMPORT \ EXPORT FUNCTIONS (not used - we use the i\e functions into \WBF\modules\options\Admin)
 */

/**
 * Replace the $old_prefix with $new_prefix in Theme Options id
 * @param $old_prefix
 * @param $new_prefix
 * @since 0.1.0
 */
function prefix_theme_options($old_prefix, $new_prefix) {
    $options_field = get_option('optionsframework');

    if (!$options_field || empty($options_field)) return;

    $options = get_option($options_field['id']);
    $new_options = array();

    if (!empty($options) && $options != false) {
        foreach ($options as $k => $v) {
            $new_k = preg_replace("|^" . $old_prefix . "_|", $new_prefix . "_", $k);
            $new_options[$new_k] = $v;
        }
    } else {
        return;
    }

    update_option($options_field['id'], $new_options);
}

/**
 * Transfer theme options from a theme to another
 * @param string $from_theme theme the name of the theme from which export
 * @param (optional) null string $to_theme the name of the theme into which import (current theme if null)
 * @totest
 * @since 0.1.0
 */
function transfer_theme_options($from_theme, $to_theme = null) {
    $from_theme_options = get_option($from_theme);
    if (!isset($to_theme))
        import_theme_options($from_theme_options);
    else
        update_option($to_theme, $from_theme_options);
}

/**
 * Copy a theme options array into current theme options option. Old theme options will be replaced.
 * @param array $exported_options
 * @totest
 * @since 0.1.0
 */
function import_theme_options($exported_options) {
    $options_field = get_option('optionsframework');
    update_option($options_field['id'], $exported_options);
}