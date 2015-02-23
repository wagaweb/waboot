<?php

namespace WBF\modules\components;

add_action("wbf_deactivated",'\WBF\modules\components\reset_vars');

function reset_vars($theme_switched){
	if(!empty($theme_switched)){
		$wbf_components_saved_once = (array) get_option("wbf_components_saved_once", array());
		if(($key = array_search($theme_switched, $wbf_components_saved_once)) !== false) {
			unset($wbf_components_saved_once[$key]);
		}
		if(empty($wbf_components_saved_once) ||
		   (isset($wbf_components_saved_once[0]) && empty($wbf_components_saved_once[0]) && count($wbf_components_saved_once) == 1)
		){
			delete_option( "wbf_components_saved_once" );
		}else{
			update_option( "wbf_components_saved_once", $wbf_components_saved_once );
		}
	}
}