<?php

$wbf_path = defined("WBF_DIRECTORY") ? WBF_DIRECTORY : get_option( "wbf_path" );
require_once $wbf_path."/includes/pluginsframework/autoloader.php";

class Waboot_Plugin extends WBF\includes\pluginsframework\Plugin {}