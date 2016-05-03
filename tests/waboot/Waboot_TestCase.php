<?php

namespace tests;

class Waboot_TestCase extends \PHPUnit_Framework_TestCase {
	var $waboot_path;

	public function setUp(){
		\WP_Mock::setUp();

		$this->waboot_path = WBTEST_WP_CONTENT_PATH."/themes/waboot";

		//Loads WBF
		\WP_Mock::wpFunction( 'get_bloginfo', array(
			'args' => ['url'],
			'return' => "http://waboot.dev"
		) );

		\WP_Mock::wpFunction('wp_parse_args', [
			'args' => [
				[],
				[
					'do_global_theme_customizations' => true,
					'check_for_updates' => true
				]
			],
			'return' => [
				'do_global_theme_customizations' => true,
				'check_for_updates' => true
			]
		]);

		\WP_Mock::wpFunction('get_option',[
			'args' => ["wbf_installed"],
			'return' => true
		]);

		\WP_Mock::wpFunction('get_option',[
			'args' => ["wbf_path"],
			'return' => WBTEST_WP_CONTENT_PATH."/plugins/wbf"
		]);

		\WP_Mock::wpFunction('get_option',[
			'args' => ["wbf_url"],
			'return' => "http://waboot.dev/plugins/wbf"
		]);

		\WP_Mock::wpFunction('get_template_directory',[
			'args' => [],
			'return' => WBTEST_WP_CONTENT_PATH."/themes/waboot"
		]);

		\WP_Mock::wpFunction('get_stylesheet_directory',[
			'args' => [],
			'return' => WBTEST_WP_CONTENT_PATH."/themes/waboot"
		]);

		\WP_Mock::wpFunction('wp_get_theme',[
			'args' => [],
			'return' => new \WP_Theme()
		]);

		\WP_Mock::wpFunction('plugin_basename',[
			'args' => [WBTEST_WP_CONTENT_PATH."/plugins/wbf/wbf.php"],
			'return' => "wbf"
		]);

		require_once WBTEST_WP_CONTENT_PATH."/plugins/wbf/wbf.php";
	}
}