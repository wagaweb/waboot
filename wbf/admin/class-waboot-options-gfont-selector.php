<?php

add_action("wp_ajax_gfontfetcher_getFonts",'Waboot_Options_GFont_Selector::getFonts');
add_action("wp_ajax_nopriv_gfontfetcher_getFonts",'Waboot_Options_GFont_Selector::getFonts');
add_action("wp_ajax_gfontfetcher_getFontInfo",'Waboot_Options_GFont_Selector::getFontInfo');
add_action("wp_ajax_nopriv_gfontfetcher_getFontInfo",'Waboot_Options_GFont_Selector::getFontInfo');

class Waboot_Options_GFont_Selector
{
	public function init(){
		add_action('admin_enqueue_scripts',array($this, 'scripts'));
		add_action('wp_enqueue_scripts',array($this, 'loadFonts'));
	}

	function scripts( $hook ) {
		if ( ! wbf_is_admin_of_page( $hook ) ) {
			return;
		}

		wp_register_script('font-selector', WBF_URL . '/admin/js/font-selector.js',array('jquery'));
		wp_enqueue_script('font-selector');
	}

    /**
     * Loads the fonts into wordpress head
     */
    function loadFonts(){
        $options_names = apply_filters("wbf_of_gfonts_options",array()); //the name of the options that the theme uses for gfonts
        $fonts_to_load = array();
        foreach($options_names as $opt_name){
            $value = of_get_option($opt_name);
            $font_name = preg_replace("/ /","+",$value['family']);
            if(!isset($fonts_to_load[$font_name])){
                $fonts_to_load[$font_name] = array(
                    'styles' => array(),
                    'subsets' => array()
                );
            }
            if(!in_array($value['style'],$fonts_to_load[$font_name]['styles']))
                $fonts_to_load[$font_name]['styles'][] = $value['style'];
            if(!in_array($value['charset'],$fonts_to_load[$font_name]['subsets']))
                $fonts_to_load[$font_name]['subsets'][] = $value['charset'];
        }
        foreach($fonts_to_load as $name => $props){
            $font_string = $name;
            if(isset($props['styles'])) $font_string .= ":";
            $i = 0;
            foreach($props['styles'] as $style){
                $font_string .= $style;
                if($i != count($props['styles']) - 1)
                    $font_string .= ",";
                $i++;
            }
            if(isset($props['subsets'])){
                $font_string .= "&subset:";
                $i = 0;
                foreach($props['subsets'] as $subset){
                    $font_string .= $subset;
                    if($i != count($props['subsets']) - 1)
                        $font_string .= ",";
                    $i++;
                }
            }
            ?>
            <link rel='stylesheet' id="options_gfont_<?php echo $name; ?>" href='http://fonts.googleapis.com/css?family=<?php echo $font_string ?>' type='text/css' media="all">
            <?php
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
		$output .= "<select class='font-family-selector' name='".self::fontFamily_OptName($option_name,$id)."'>";
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
		$output .= "<select class='font-style-selector' name='".self::fontStyles_OptName($option_name,$id)."'>";
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
		$output .= "<select class='font-charset-selector' name='".self::fontCharset_OptName($option_name,$id)."'>";
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
		$output .= '<input name="' . self::fontColor_OptName($option_name,$id) . '" id="' . $id . '" class="of-color font-color-selector"  type="text" value="' . esc_attr($current_color) . '"' . $default_color . ' />';

        /*
         * Category
         */
        $category = isset($value['category']) && !empty($value['category'])? $value['category'] : "";
        $output .= "<input class='font-category-selector' type='hidden' name='".self::fontCategory_OptName($option_name,$id)."' value='".$category."' />";

		return $output;
	}

	static function getFonts(){
		$gfontfetcher = \WBF\GoogleFontsRetriever::getInstance();
		return $gfontfetcher->cached_fonts;
	}

	static function getFontInfo($familyname = ""){
		if(empty($familyname)){
			if(isset($_POST['family'])){
				$familyname = $_POST['family'];
			}
			else{
				if(DOING_AJAX){
					echo "0";
					die();
				}
				else return false;
			}
		}
		$gfontfetcher = \WBF\GoogleFontsRetriever::getInstance();
		if(DOING_AJAX){
			$font_info = $gfontfetcher->get_properties_of($familyname);
			if(!$font_info) echo "0";
			else{
				echo json_encode(array(
					'family' => $font_info->family,
					'variants' => $font_info->variants,
					'subsets' => $font_info->subsets,
                    'category' => $font_info->category
				));
				die();
			}
		}else{
			return $gfontfetcher->get_properties_of($familyname);
		}
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

    private static function fontCategory_OptName($theme_name,$opt_id){
        return $theme_name.'['.$opt_id.'][category]';
    }

	private static function fontElements_OptName($theme_name,$opt_id){
		return $theme_name.'['.$opt_id.'][elements]';
	}
}