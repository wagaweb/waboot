<?php
/*
 * This file is required during deploy_theme_options_css(). It contains functions usable within the _theme-options.src
 */

if(!function_exists("waboot_styles_adjustBrightness")):
	/**
	 * Adjust the color brightness given the hex and the steps
	 *
	 * @param $args
	 *
	 * @return string
	 */
	function waboot_styles_adjustBrightness($args) {
		$hex = $args[0];
		$steps = $args[1];

		// Steps should be between -255 and 255. Negative = darker, positive = lighter
		$steps = max(-255, min(255, $steps));

		// Normalize into a six character long hex string
		$hex = str_replace('#', '', $hex);
		if (strlen($hex) == 3) {
			$hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
		}

		// Split into three parts: R, G and B
		$color_parts = str_split($hex, 2);
		$return = '#';

		foreach ($color_parts as $color) {
			$color   = hexdec($color); // Convert to decimal
			$color   = max(0,min(255,$color + $steps)); // Adjust color
			$return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
		}

		return $return;
	}
endif;