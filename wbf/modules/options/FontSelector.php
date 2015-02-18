<?php

namespace WBF\modules\options;

class FontSelector
{
	public function init(){
		add_action('admin_enqueue_scripts',array($this, 'scripts'));
		add_action('wp_enqueue_scripts',array($this, 'loadFonts'));
	}

	function scripts( $hook ) {
		if ( ! of_is_admin_framework_page( $hook ) ) {
			return;
		}

        wp_register_script('gfont_loader','http://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js');
        if(WABOOT_ENV == "dev"){
            wp_register_script('font-selector', WBF_URL . '/sources/js/admin/font-selector.js',array('jquery','gfont_loader','underscore'));
        }else{
            wp_register_script('font-selector', WBF_URL . '/admin/js/font-selector.min.js',array('jquery','gfont_loader','underscore'));
        }
        $fonts_to_load = $this->getWebFontsToLoad();
        $families = array();
        $i = 0;
        foreach($fonts_to_load as $name => $props){
            $name = preg_replace("/\+/"," ",$name);
            $families[] = $name;
        }
        wp_localize_script('font-selector','wbfOfFonts',array(
            'families' => $families
        ));
		wp_enqueue_script('font-selector');
	}

    /**
     * Loads the fonts into wordpress head
     */
    function loadFonts(){
        $fonts_to_load = $this->getWebFontsToLoad();
        foreach($fonts_to_load as $name => $props){
            if(!self::isOSFont(preg_replace("/ /","+",$name))){
                echo $this->buildFontString($name,$props);
            }
        }
    }

    function getWebFontsToLoad(){
        $options_names = apply_filters("wbf_of_gfonts_options",array()); //the name of the options that the theme uses for gfonts
        $fonts_to_load = array();
        foreach($options_names as $opt_name){
            $value = of_get_option($opt_name);
            if($value && !self::isOSFont($value['family'])){
                $font_name = preg_replace("/ /","+",$value['family']);
                if(!isset($fonts_to_load[$font_name])){
                    $fonts_to_load[$font_name] = array(
                        'styles' => array(),
                        'subsets' => array()
                    );
                }
                if($value['style'] == "") $value['style'] = array();
                if($value['charset'] == "") $value['charset'] = array();
	            if(!is_array($value['style'])) $value['style'] = array($value['style']);
	            if(!is_array($value['charset'])) $value['charset'] = array($value['charset']);
                foreach($value['style'] as $style){
                    if(!in_array($style,$fonts_to_load[$font_name]['styles']))
                        $fonts_to_load[$font_name]['styles'][] = $style;
                }
                foreach($value['charset'] as $charset){
                    if(!in_array($charset,$fonts_to_load[$font_name]['subsets']))
                        $fonts_to_load[$font_name]['subsets'][] = $charset;
                }
            }
        }
        return $fonts_to_load;
    }

    function buildFontString($name,$props,$return = "css"){
        $font_string = $name;
        $i = 0;
        foreach($props['styles'] as $style){
	        if($i==0) $font_string .= ":";
            $font_string .= $style;
            if($i != count($props['styles']) - 1)
                $font_string .= ",";
            $i++;
        }
        if(isset($props['subsets']) && !empty($props['subsets'])){
            $font_string .= "&subset:";
            $i = 0;
            foreach($props['subsets'] as $subset){
                $font_string .= $subset;
                if($i != count($props['subsets']) - 1)
                    $font_string .= ",";
                $i++;
            }
        }
        $css = "<link rel='stylesheet' id='options_gfont_$name' href='http://fonts.googleapis.com/css?family=$font_string' type='text/css' media='all'>";
        if($return == "css")
            return $css;
        else
            return $font_string;
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

		$output = ''; $id = strip_tags(strtolower($_id)); $class = "of-input gfont"; $int = ''; $value = '';
		$name = $_name != '' ? $_name : $option_name . '[' . $id . ']';

		$fonts = self::getFonts();

		$defaults = $_defaults;
        if(!is_array($defaults['style'])) $defaults['style'] = array($defaults['style']);
        if(!is_array($defaults['charset'])) $defaults['charset'] = array($defaults['charset']);

		// If a value is passed and we don't have a stored value, use the value that's passed through.
		if ( $_value != '' && $value == '' ) {
			$value = $_value;
            if($value['style'] == "") $value['style'] = array();
            if($value['charset'] == "") $value['charset'] = array();
            if(!is_array($value['style'])) $value['style'] = array($value['style']);
            if(!is_array($value['charset'])) $value['charset'] = array($value['charset']);
		}

		if(!empty($value)){
			$selected_font = self::isOSFont($value['family']) ? self::getOSFontProps($value['family']) : $wbf_gfont_fetcher->get_properties_of($value['family']);
			if(!$selected_font) $selected_font = $fonts[0];
			$selected_font->color = $value['color'];
			if(!isset($selected_font->category)){
				$selected_font->category = isset($value['category']) ? $value['category'] : "";
			}
		}else{
			$selected_font = new stdClass();
			$selected_font->family = $defaults['family'];
			$selected_font->variants = $defaults['style'];
			$selected_font->subsets = $defaults['charset'];
			$selected_font->color = $defaults['color'];
			$selected_font->category = isset($defaults['category']) ? $defaults['category'] : "";
		}
		$selected_font->family_slug = preg_replace("/ /","-",$selected_font->family);

		/**
		 * FAMILY
		 */
		$output .= "<select class='font-family-selector' name='".self::fontFamily_OptName($option_name,$id)."'>";
		foreach($fonts as $font){
			if($selected_font->family == $font->family){
				$output .= "<option value='$font->family' selected>$font->family</option>";
			}else{
				$output .= "<option value='$font->family'>$font->family</option>";
			}
		}
		$output .= "</select>";

		/**
		 * COLOR
		 */
		$current_color = $selected_font->color;
		$default_color = ' data-default-color="' . $defaults['color'] . '" ';
		$output .= '<input name="' . self::fontColor_OptName($option_name,$id) . '" id="' . $id . '" class="of-color font-color-selector"  type="text" value="' . esc_attr($current_color) . '"' . $default_color . ' />';

		/**
		 * VARIANTS
		 */
        $output .= "<div class='font-style-selector'>";
		foreach($selected_font->variants as $variant){
			if(!empty($value) && in_array($variant,$value['style'])){
                $output .= "<div class='check-wrapper'><input type=\"checkbox\" name=\"".self::fontStyles_OptName($option_name,$id)."[]\" value=\"$variant\" class=\"check $selected_font->family_slug\" checked>$variant</div>";
			}elseif(empty($value) && in_array($variant,$defaults['style'])){
                $output .= "<div class='check-wrapper'><input type=\"checkbox\" name=\"".self::fontStyles_OptName($option_name,$id)."[]\" value=\"$variant\" class=\"check $selected_font->family_slug\" checked>$variant</div>";
            }
            else{
                $output .= "<div class='check-wrapper'><input type=\"checkbox\" name=\"".self::fontStyles_OptName($option_name,$id)."[]\" value=\"$variant\" class=\"check $selected_font->family_slug\">$variant</div>";
			}
		}
        $output .= "</div>";

		/**
		 * SUBSETS
		 */
        $output .= "<div class='font-charset-selector'>";
		foreach($selected_font->subsets as $subset){
			if(!empty($value) && in_array($subset,$value['charset'])){
                $output .= "<div class='check-wrapper'><input type=\"checkbox\" name=\"".self::fontCharset_OptName($option_name,$id)."[]\" value=\"$subset\" class=\"check\" checked>$subset</div>";
			}elseif(empty($value) && in_array($subset,$defaults['charset'])){
                $output .= "<div class='check-wrapper'><input type=\"checkbox\" name=\"".self::fontCharset_OptName($option_name,$id)."[]\" value=\"$subset\" class=\"check\" checked>$subset</div>";
            }
            else{
                $output .= "<div class='check-wrapper'><input type=\"checkbox\" name=\"".self::fontCharset_OptName($option_name,$id)."[]\" value=\"$subset\" class=\"check\">$subset</div>";
			}
		}
        $output .= "</div>";

        /*
         * Category
         */
        $output .= "<input class='font-category-selector' type='hidden' name='".self::fontCategory_OptName($option_name,$id)."' value='".$selected_font->category."' />";

        /*
         * PREVIEW
         */
        $ff = $selected_font->category != "" ? "'".$selected_font->family."',".$selected_font->category : "'".$selected_font->family."'";
        $output .= "<div class='font-preview'>
        <p style=\"font-family: ".$ff."; \">Lorem ipsum dolor sit <strong>amet</strong>, consectetur adipiscing <em>elit</em>, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
        </div>";

		return $output;
	}

	static function getFonts(){
		$gfontfetcher = \WBF\GoogleFontsRetriever::getInstance();

		$os_fonts = self::getOSFonts();
		$g_fonts = $gfontfetcher->get_webfonts();
		if(!$g_fonts){
			$g_fonts = new stdClass();
			$g_fonts->items = array();
		}
		$fonts = array_merge($os_fonts,$g_fonts->items);

		return $fonts;
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
            if(self::isOSFont($familyname)){
                $font_info = self::getOSFontProps($familyname);
            }else{
                $font_info = $gfontfetcher->get_properties_of($familyname);
            }
			if(!$font_info) echo "0";
			else{
				echo json_encode(array(
					'family' => $font_info->family,
					'variants' => $font_info->variants,
					'subsets' => $font_info->subsets,
                    'category' => $font_info->category,
                    'kind' => $font_info->kind
				));
				die();
			}
		}else{
            if(self::isOSFont($familyname)){
                return self::getOSFontProps($familyname);
            }else{
                return $gfontfetcher->get_properties_of($familyname);
            }
		}
	}

    static function isOSFont($familyname){
        $osFonts = self::getOSFonts();
        foreach($osFonts as $font){
            if($font->family == $familyname){
                return true;
            }
        }

        return false;
    }

    static function getOSFontProps($familyname){
        $osFonts = self::getOSFonts();
        foreach($osFonts as $font){
            if($font->family == $familyname){
                return $font;
            }
        }
        return false;
    }

    static public function getOSFonts(){
        $osFonts = array(
            array(
                'kind' => 'osfonts#osfont',
                'family' => 'Arial',
                'category' => 'sans-serif',
                'variants' => array('regular'),
                'subsets' => array('latin'),
            ),
            array(
                'kind' => 'osfonts#osfont',
                'family' => 'Avant Garde',
                'category' => 'sans-serif',
                'variants' => array('regular'),
                'subsets' => array('latin'),
            ),
            array(
                'kind' => 'osfonts#osfont',
                'family' => 'Cambria, Georgia',
                'category' => 'serif',
                'variants' => array('regular'),
                'subsets' => array('latin'),
            ),
            array(
                'kind' => 'osfonts#osfont',
                'family' => 'Copse',
                'category' => 'sans-serif',
                'variants' => array('regular'),
                'subsets' => array('latin'),
            ),
            array(
                'kind' => 'osfonts#osfont',
                'family' => 'Garamond, Hoefler Text, Times New Roman, Times',
                'category' => 'serif',
                'variants' => array('regular'),
                'subsets' => array('latin'),
            ),
            array(
                'kind' => 'osfonts#osfont',
                'family' => 'Georgia',
                'category' => 'serif',
                'variants' => array('regular'),
                'subsets' => array('latin'),
            ),
            array(
                'kind' => 'osfonts#osfont',
                'family' => 'Helvetica Neue, Helvetica',
                'category' => 'sans-serif',
                'variants' => array('regular'),
                'subsets' => array('latin'),
            ),
            array(
                'kind' => 'osfonts#osfont',
                'family' => 'Tahoma, Geneva',
                'category' => 'sans-serif',
                'variants' => array('regular'),
                'subsets' => array('latin'),
            ),
        );
        $osFonts = apply_filters("wbf_of_typography_osFonts",$osFonts);
        $result = array();
        foreach($osFonts as $font){
            $fontObj = new \stdClass();
            $fontObj->kind = $font['kind'];
            $fontObj->family = $font['family'];
            $fontObj->category = $font['category'];
            $fontObj->variants = $font['variants'];
            $fontObj->subsets = $font['subsets'];
            array_push($result,$fontObj);
        }

        return $result;
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