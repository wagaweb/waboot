<?php

class WP_ThemeTest extends WP_UnitTestCase{
	public function setUp() {
		parent::setUp();
		switch_theme('waboot');
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
	
	public function testHooks(){
		global $wp_filter;
	}
}