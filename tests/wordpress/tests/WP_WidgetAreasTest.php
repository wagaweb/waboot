<?php

class WP_WidgetAreasTest extends WP_UnitTestCase{
	public function setUp() {
		parent::setUp();
		WBF()->startup();
	}

	function tearDown(){
		parent::tearDown();
	}

	public function test_WidetsAreaRendering_1widget(){
		global $_wp_sidebars_widgets, $sidebars_widgets;
		$index = "footer";
		$_wp_sidebars_widgets[$index."-1"] = true;

		$this->expectOutputRegex("/col-sm-12/");

		\Waboot\functions\print_widgets_in_area("footer");
	}

	public function test_WidetsAreaRendering_2widgets(){
		global $_wp_sidebars_widgets, $sidebars_widgets;
		$index = "footer";
		$_wp_sidebars_widgets[$index."-1"] = true;
		$_wp_sidebars_widgets[$index."-2"] = true;

		$this->expectOutputRegex("/col-sm-6/");

		\Waboot\functions\print_widgets_in_area("footer");
	}

	public function test_WidetsAreaRendering_3widgets(){
		global $_wp_sidebars_widgets, $sidebars_widgets;
		$index = "footer";
		$_wp_sidebars_widgets[$index."-1"] = true;
		$_wp_sidebars_widgets[$index."-2"] = true;
		$_wp_sidebars_widgets[$index."-3"] = true;

		$this->expectOutputRegex("/col-sm-4/");

		\Waboot\functions\print_widgets_in_area("footer");
	}

	public function test_WidetsAreaRendering_4widgets(){
		global $_wp_sidebars_widgets, $sidebars_widgets;
		$index = "footer";
		$_wp_sidebars_widgets[$index."-1"] = true;
		$_wp_sidebars_widgets[$index."-2"] = true;
		$_wp_sidebars_widgets[$index."-3"] = true;
		$_wp_sidebars_widgets[$index."-4"] = true;

		$this->expectOutputRegex("/col-sm-3/");

		\Waboot\functions\print_widgets_in_area("footer");
	}

	public function test_WidetsAreaRendering_5widget_limit4(){
		global $_wp_sidebars_widgets, $sidebars_widgets;
		$index = "footer";
		$_wp_sidebars_widgets[$index."-1"] = true;
		$_wp_sidebars_widgets[$index."-2"] = true;
		$_wp_sidebars_widgets[$index."-3"] = true;
		$_wp_sidebars_widgets[$index."-4"] = true;
		$_wp_sidebars_widgets[$index."-5"] = true;

		$this->expectOutputRegex("/col-sm-3/");

		\Waboot\functions\print_widgets_in_area("footer",4);
	}

	public function test_WidetsAreaRendering_5widget(){
		global $_wp_sidebars_widgets, $sidebars_widgets;
		$index = "footer";
		$_wp_sidebars_widgets[$index."-1"] = true;
		$_wp_sidebars_widgets[$index."-2"] = true;
		$_wp_sidebars_widgets[$index."-3"] = true;
		$_wp_sidebars_widgets[$index."-4"] = true;
		$_wp_sidebars_widgets[$index."-5"] = true;

		$this->expectOutputRegex("/col-sm-1/");

		\Waboot\functions\print_widgets_in_area("footer",5);
	}
}