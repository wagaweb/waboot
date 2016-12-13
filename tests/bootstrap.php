<?php
define("WBTEST_CURRENT_PATH",dirname(__FILE__));
define("WBTEST_WP_CONTENT_PATH",dirname(dirname(dirname(WBTEST_CURRENT_PATH))));
define("WBTEST_WORDPRESS_PATH",dirname(dirname(dirname(dirname(WBTEST_CURRENT_PATH)))));
if(!defined("ABSPATH")){
	define("ABSPATH",WBTEST_WORDPRESS_PATH."/");
}

if(!defined("WPINC")) define("WPINC",WBTEST_WORDPRESS_PATH."/wp-includes");

require_once dirname(WBTEST_CURRENT_PATH)."/vendor/autoload.php";
require_once "waboot/Waboot_TestCase.php";

class WP_Theme{
	var $stylesheet;
	var $template;

	public function __construct(){
		$this->stylesheet = "waboot";
		$this->template = "waboot";
	}

	public function get_stylesheet(){
		return $this->stylesheet;
	}

	public function get_template(){
		return $this->template;
	}
}