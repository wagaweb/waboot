<?php

$wbf_path = get_option( "wbf_path" );
require_once $wbf_path."/includes/pluginsframework/autoloader.php";

class Waboot_Template_Plugin extends WBF\includes\pluginsframework\TemplatePlugin {}