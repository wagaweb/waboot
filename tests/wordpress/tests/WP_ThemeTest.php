<?php

class WP_ThemeTest extends WP_UnitTestCase{
	function testEnv() {
		//Check the theme
		$this->assertEquals( 'waboot' , wp_get_theme()->template );

		//Check WBF
		$plugins = get_option('active_plugins', []);
		$this->assertTrue(is_array($plugins));
		$this->assertTrue(in_array("wbf/wbf.php",$plugins));
	}
}