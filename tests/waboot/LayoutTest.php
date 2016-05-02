<?php

namespace tests;

use Patchwork\Exceptions\Exception;
use Waboot\Layout;
use WBF\includes\mvc\HTMLView;

class LayoutTest extends \PHPUnit_Framework_TestCase{
	/**
	 * @var Layout
	 */
	var $layout;

	public function setUp() {
		\WP_Mock::setUp();

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

		//Loads Layout
		require_once WBTEST_WP_CONTENT_PATH."/themes/waboot/inc/Layout.php";
		
		$this->layout = Layout::getInstance();
	}

	/**
	 * Checks if Layout can be correctly created
	 */
	public function testLayoutExists(){
		$this->assertInstanceOf("Waboot\\Layout",$this->layout);
	}

	/**
	 * Testing the creare zone mechanism
	 */
	public function testCreateZones(){
		\WP_Mock::wpFunction('get_template_directory',[
			'args' => [],
			'return' => WBTEST_WP_CONTENT_PATH."/themes/waboot"
		]);

		\WP_Mock::wpFunction('get_stylesheet_directory',[
			'args' => [],
			'return' => WBTEST_WP_CONTENT_PATH."/themes/waboot"
		]);

		\WP_Mock::wpFunction('wp_parse_args', [
			'args' => [
				['always_load' => true],
				['always_load' => false]
			],
			'return' => [
				['always_load' => true ]
			]
		]);
		
		$this->layout->create_zone("header",new HTMLView("templates/header.php"),["always_load"=>true]);

		$zones = $this->layout->getZones();
		$this->assertEquals(true,is_array($zones)); //The zones is an array?
		$this->assertEquals(1,count($zones)); //The zones as 1 element?
		$this->assertArrayHasKey("header",$zones); //We have our zone?
	}

	public function testCreateZonesWithErrors_1(){
		$this->add_zones_mocks();
		
		try{
			$this->layout->create_zone("head er",new HTMLView("templates/header.php")); //The slug cannot have spaces
			$this->fail();
		}catch(\Exception $e){}
	}
	
	public function testCreateZonesWithErrors_2(){
		$this->add_zones_mocks();

		try{
			$this->layout->create_zone("header",new \stdClass()); //The template must be a string, an array or a View
			$this->fail();
		}catch(\Exception $e){}
	}

	public function testCreateZonesWithErrors_3(){
		$this->add_zones_mocks();

		try{
			$this->layout->create_zone("header",12); //The template must be a string, an array or a View
			$this->fail();
		}catch(\Exception $e){}
	}

	public function testCreateZonesWithErrors_4(){
		$this->add_zones_mocks();

		try{
			$this->layout->create_zone("header",new HTMLView("templates/header.php"),false); //The args must be an array
			$this->fail();
		}catch(\Exception $e){}
	}

	private function add_zones_mocks(){
		\WP_Mock::wpFunction('get_template_directory',[
			'args' => [],
			'return' => WBTEST_WP_CONTENT_PATH."/themes/waboot"
		]);

		\WP_Mock::wpFunction('get_stylesheet_directory',[
			'args' => [],
			'return' => WBTEST_WP_CONTENT_PATH."/themes/waboot"
		]);

		\WP_Mock::wpFunction('wp_parse_args', [
			'args' => [
				['always_load' => true],
				['always_load' => false]
			],
			'return' => [
				['always_load' => true ]
			]
		]);

		\WP_Mock::wpFunction('locate_template', [
			'args' => [\WP_Mock\Functions::type("string")],
			'return' => true
		]);
	}

	public function tearDown() {
		\WP_Mock::tearDown();
	}
}