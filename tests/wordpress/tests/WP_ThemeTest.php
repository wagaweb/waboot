<?php

namespace tests;

class WP_ThemeTest extends WP_UnitTestCase{
	function testSample() {
		$this->assertTrue( 'Your Theme' == wp_get_theme() );
		$this->assertTrue( is_plugin_active('your-plugin/your-plugin.php') );
	}
}