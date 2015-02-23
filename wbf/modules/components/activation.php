<?php

namespace WBF\modules\components;

add_action("wbf_activated",'\WBF\modules\components\enable_default_components');

function enable_default_components(){
	if(class_exists('\WBF\modules\components\ComponentsManager')){
		$theme = wp_get_theme();
		$components_already_saved = (array) get_option( "wbf_components_saved_once", array() );
		if(!in_array($theme->get_stylesheet(),$components_already_saved)){
			$default_components = apply_filters("wbf_default_components",array());
			foreach($default_components as $c_name){
				ComponentsManager::ensure_enabled($c_name);
			}
		}
	}
}
