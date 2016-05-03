<?php

class WP_WidgetAreasTest extends WP_UnitTestCase{
	public function setUp() {
		parent::setUp();
		WBF()->startup();
	}

	function tearDown(){
		parent::tearDown();
	}

	public function test_WidetsAreaRendering(){
		$prefix = "footer";
		$count = 4;
		$sidebar_class = "col-sm-3";

		global $_wp_sidebars_widgets, $sidebars_widgets;
		$index = $prefix;
		$_wp_sidebars_widgets[$index."-1"] = true;
		$_wp_sidebars_widgets[$index."-2"] = true;
		$_wp_sidebars_widgets[$index."-3"] = true;
		$_wp_sidebars_widgets[$index."-4"] = true;
		
		$this->expectOutputRegex("/col-sm-3/");

		\Waboot\functions\print_widgets_in_area("footer");
	}
}