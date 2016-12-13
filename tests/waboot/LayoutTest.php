<?php

namespace tests;

use Patchwork\Exceptions\Exception;
use Waboot\Layout;
use WBF\components\mvc\HTMLView;

class LayoutTest extends Waboot_TestCase{
	/**
	 * @var Layout
	 */
	var $layout;

	public function setUp() {
		parent::setUp();

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