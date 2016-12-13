<?php

namespace tests;

class WidgetAreasTest extends Waboot_TestCase{
	public function setUp() {
		parent::setUp();

		\WP_Mock::wpFunction("is_active_sidebar",[
			'args' => [
				\WP_Mock\Functions::anyOf("footer-1","footer-2","footer-3",'footer-4')
			],
			'return' => true
		]);
		
		require_once $this->waboot_path."/inc/template-functions.php";
	}

	public function test_WidgetsCounter(){
		$count = \Waboot\functions\count_widgets_in_area("footer");
		$this->assertEquals(4,$count);
	}
}