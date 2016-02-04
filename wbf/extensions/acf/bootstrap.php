<?php

namespace WBF\extensions\acf;

// ACF INTEGRATION
if(!is_plugin_active("advanced-custom-fields-pro/acf.php") && !is_plugin_active("advanced-custom-fields/acf.php")){
	require_once \WBF::get_path().'vendor/acf/acf.php';
	require_once 'acf-integration.php';
}