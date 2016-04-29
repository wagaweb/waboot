<?php

class WP_ThemeTest extends WP_UnitTestCase{
	public function setUp() {
		parent::setUp();
		WBF()->startup();
	}

	function tearDown(){
		parent::tearDown();
	}

	public function testEnv() {
		//Check the theme
		$this->assertEquals( 'waboot' , wp_get_theme()->template );

		//Check WBF
		$plugins = get_option('active_plugins', []);
		$this->assertTrue(is_array($plugins));
		$this->assertTrue(in_array("wbf/wbf.php",$plugins));
		$this->assertTrue(class_exists("WBF"));
	}
	
	public function testWBFMenuLabelRenamed(){
		$label = apply_filters("wbf/admin_menu/label","WBF");
		$this->assertEquals("Waboot",$label);
	}

	public function testNavMenus(){
		$nav_menus = get_registered_nav_menus();
		$this->assertTrue(array_key_exists("top",$nav_menus));
		$this->assertTrue(array_key_exists("main",$nav_menus));
		$this->assertTrue(array_key_exists("bottom",$nav_menus));
	}

	public function testHasWidgetAreas(){
		global $wp_registered_sidebars;
		$areas = \Waboot\functions\get_widget_areas();
		
		foreach($areas as $name => $args){
			$this->assertTrue(array_key_exists($name,$wp_registered_sidebars));
		}
	}
}