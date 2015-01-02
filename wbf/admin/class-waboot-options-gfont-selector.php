<?php

class Waboot_Options_GFont_Selector
{
	public function init(){

	}

	function scripts( $hook ) {
		if ( ! wbf_is_admin_of_page( $hook ) ) {
			return;
		}
	}

	/**
	 * @param string $_id - A token to identify this field (the name).
	 * @param string $_value - The value of the field, if present.
	 * @param string $_defaults - The defaults value of the field..
	 * @param string $_desc - An optional description of the field.
	 * @param string $_name
	 *
	 * @return string
	 */
	static function output($_id, $_value, $_defaults, $_desc = '', $_name = ''){
		global $wbf_gfont_fetcher;
		$optionsframework_settings = get_option( 'optionsframework' );

		// Gets the unique option id
		$option_name = $optionsframework_settings['id'];

		$output = '';
		$id = '';
		$class = '';
		$int = '';
		$value = '';
		$defaults = $_defaults;
		$name = '';

		$id = strip_tags( strtolower( $_id ) );

		// If a value is passed and we don't have a stored value, use the value that's passed through.
		if ( $_value != '' && $value == '' ) {
			$value = $_value;
		}

		if ( $_name != '' ) {
			$name = $_name;
		} else {
			$name = $option_name . '[' . $id . ']';
		}

		$class = "of-input gfont";

		$fonts = $wbf_gfont_fetcher->get_webfonts();

		/**
		 * FAMILY
		 */
		$output .= "<select name='".self::fontFamily_OptName($option_name,$id)."'>";
		foreach($fonts->items as $font){
			if(!empty($value) && $value['family'] == $font->family){
				$output .= "<option value='$font->family' selected>$font->family</option>";
			}else{
				$output .= "<option value='$font->family'>$font->family</option>";
			}
		}
		$output .= "</select>";

		if(!empty($value)){
			$selected_font_props = $wbf_gfont_fetcher->get_properties_of($value['family']);
			if(!$selected_font_props) $selected_font_props = $fonts->items[0];
		}else{
			$selected_font_props = $fonts->items[0];
		}

		/**
		 * VARIANTS
		 */
		$output .= "<select name='".self::fontStyles_OptName($option_name,$id)."'>";
		foreach($selected_font_props->variants as $variant){
			if(!empty($value) && $value['style'] == $variant){
				$output .= "<option value='$variant' selected>$variant</option>";
			}else{
				$output .= "<option value='$variant'>$variant</option>";
			}
		}
		$output .= "</select>";
		/**
		 * SUBSETS
		 */
		$output .= "<select name='".self::fontCharset_OptName($option_name,$id)."'>";
		foreach($selected_font_props->subsets as $subset){
			if(!empty($value) && $value['charset'] == $subset){
				$output .= "<option value='$subset' selected>$subset</option>";
			}else{
				$output .= "<option value='$subset'>$subset</option>";
			}
		}
		$output .= "</select>";
		/**
		 * COLOR
		 */
		$current_color = '';
		$default_color = ' data-default-color="' . $defaults['color'] . '" ';
		if(isset($value['color']) && $value['color'] != "") {
			$current_color = $value['color'];
		}else{
			$current_color = $defaults['color'];
		}
		$output .= '<input name="' . self::fontColor_OptName($option_name,$id) . '" id="' . $id . '" class="of-color"  type="text" value="' . esc_attr($current_color) . '"' . $default_color . ' />';

		return $output;
	}

	private static function fontFamily_OptName($theme_name,$opt_id){
		return $theme_name.'['.$opt_id.'][family]';
	}

	private static function fontStyles_OptName($theme_name,$opt_id){
		return $theme_name.'['.$opt_id.'][style]';
	}

	private static function fontCharset_OptName($theme_name,$opt_id){
		return $theme_name.'['.$opt_id.'][charset]';
	}

	private static function fontColor_OptName($theme_name,$opt_id){
		return $theme_name.'['.$opt_id.'][color]';
	}

	private static function fontElements_OptName($theme_name,$opt_id){
		return $theme_name.'['.$opt_id.'][elements]';
	}
}