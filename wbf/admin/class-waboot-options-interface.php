<?php

class Waboot_Options_Interface extends Options_Framework_Interface
{

    /**
     * Generates the options fields that are used in the form.
     */
    static function optionsframework_fields($options = null)
    {

        global $allowedtags;
        $optionsframework_settings = get_option('optionsframework');

        // Gets the unique option id
        if (isset($optionsframework_settings['id'])) {
            $option_name = $optionsframework_settings['id'];
        } else {
            $option_name = 'optionsframework';
        };

        $settings = get_option($option_name);
        if(!isset($options))
            $options = &Waboot_Options_Framework::_optionsframework_options();

        $counter = 0;
        $menu = '';

        foreach ($options as $value) {

            $val = '';
            $select_value = '';
            $output = '';

            // Set default value to $val
            if (isset($value['std'])) {
                $val = $value['std'];
            }

            // If the option is already saved, override $val
            if (($value['type'] != 'heading') && ($value['type'] != 'info')) {
                if (isset($settings[($value['id'])])) {
                    $val = $settings[($value['id'])];
                    // Striping slashes of non-array options
                    if (!is_array($val)) {
                        $val = stripslashes($val);
                    }
                }
            }

            // If there is a description save it for labels
            $explain_value = '';
            if (isset($value['desc'])) {
                $explain_value = $value['desc'];
            }

            // Wrap all options
            if (($value['type'] != "heading") && ($value['type'] != "info")) {

                // Keep all ids lowercase with no spaces
                $value['id'] = preg_replace('/[^a-zA-Z0-9._\-]/', '', strtolower($value['id']));

                $id = 'section-' . $value['id'];

                $class = 'section';
                if (isset($value['type'])) {
                    $class .= ' section-' . $value['type'];
                }
                if (isset($value['class'])) {
                    $class .= ' ' . $value['class'];
                }

                $output .= '<div id="' . esc_attr($id) . '" class="' . esc_attr($class) . '">' . "\n";
                if (isset($value['name'])) {
                    $output .= '<h4 class="heading">' . esc_html($value['name']) . '</h4>' . "\n";
                }
                if (($value['type'] != "heading") && ($value['type'] != "info")) {
                    if (($value['type'] != "checkbox") && ($value['type'] != "editor")) {
                        $output .= '<div class="explain">' . wp_kses($explain_value, $allowedtags) . '</div>' . "\n";
                    }
                }
                if ($value['type'] != 'editor') {
                    $output .= '<div class="option">' . "\n" . '<div class="controls">' . "\n";
                } else {
                    $output .= '<div class="option">' . "\n" . '<div>' . "\n";
                }
            }

            if (has_filter('optionsframework_' . $value['type'])) {
                $output .= apply_filters('optionsframework_' . $value['type'], $option_name, $value, $val);
            }


            switch ($value['type']) {

                // Basic text input
                case 'text':
                    $output .= '<input id="' . esc_attr($value['id']) . '" class="of-input" name="' . esc_attr($option_name . '[' . $value['id'] . ']') . '" type="text" value="' . esc_attr($val) . '" />';
                    break;

                // Password input
                case 'password':
                    $output .= '<input id="' . esc_attr($value['id']) . '" class="of-input" name="' . esc_attr($option_name . '[' . $value['id'] . ']') . '" type="password" value="' . esc_attr($val) . '" />';
                    break;

                // Waboot CSS Editor [WABOOT MOD]
                case "csseditor":
                    $output .= Waboot_Options_Code_Editor::optionsframework_codeditor($value['id'], $val, null);
                    break;

                // Typography [WABOOT MOD]
	            // Waboot GFont Selector [WABOOT MOD]
                case 'typography':
                case "gfont":
					$output .= Waboot_Options_Font_Selector::output($value['id'], $val, $value['std']);
					break;

                // Textarea
                case 'textarea':
                    $rows = '8';

                    if (isset($value['settings']['rows'])) {
                        $custom_rows = $value['settings']['rows'];
                        if (is_numeric($custom_rows)) {
                            $rows = $custom_rows;
                        }
                    }

                    $val = stripslashes($val);
                    $output .= '<textarea id="' . esc_attr($value['id']) . '" class="of-input" name="' . esc_attr($option_name . '[' . $value['id'] . ']') . '" rows="' . $rows . '">' . esc_textarea($val) . '</textarea>';
                    break;

                // Select Box
                case 'select':
                    $output .= '<select class="of-input" name="' . esc_attr($option_name . '[' . $value['id'] . ']') . '" id="' . esc_attr($value['id']) . '">';

                    foreach ($value['options'] as $key => $option) {
                        $output .= '<option' . selected($val, $key, false) . ' value="' . esc_attr($key) . '">' . esc_html($option) . '</option>';
                    }
                    $output .= '</select>';
                    break;


                // Radio Box
                case "radio":
                    $name = $option_name . '[' . $value['id'] . ']';
                    foreach ($value['options'] as $key => $option) {
                        $id = $option_name . '-' . $value['id'] . '-' . $key;
                        $output .= '<div class="radio-wrapper"><input class="of-input of-radio" type="radio" name="' . esc_attr($name) . '" id="' . esc_attr($id) . '" value="' . esc_attr($key) . '" ' . checked($val, $key, false) . ' /><label for="' . esc_attr($id) . '">' . esc_html($option) . '</label></div>';
                    }
                    break;

                // Image Selectors
                case "images":
                    $name = $option_name . '[' . $value['id'] . ']';
                    foreach ($value['options'] as $key => $option) {
                        $selected = '';
                        if ($val != '' && ($val == $key)) {
                            $selected = ' of-radio-img-selected';
                        }

                        if(is_array($option)){
                            $option_value = $option['value'];
                        }else{
                            $option_value = $option;
                        }

                        $output .= '<input type="radio" id="' . esc_attr($value['id'] . '_' . $key) . '" class="of-radio-img-radio" value="' . esc_attr($key) . '" name="' . esc_attr($name) . '" ' . checked($val, $key, false) . ' />';
                        $output .= '<div class="of-radio-img-label">' . esc_html($key) . '</div>';
                        $output .= '<div class="option-wrap">';
                        if(is_array($option) && isset($option['label'])){
                            $output .= '<span>'. esc_attr($option['label']) . '</span>';
                        }
                        $output .= '<img src="' . esc_url($option_value) . '" alt="' . $option_value . '" class="of-radio-img-img' . $selected . '" onclick="document.getElementById(\'' . esc_attr($value['id'] . '_' . $key) . '\').checked=true;" /></div>';
                    }
                    break;

                // Checkbox
                case "checkbox":
                    $output .= '<div class="onoffswitch">';
                    $output .= '<div class="check_wrapper"><input id="' . esc_attr($value['id']) . '" class="checkbox of-input onoffswitch-checkbox" type="checkbox" name="' . esc_attr($option_name . '[' . $value['id'] . ']') . '" ' . checked($val, 1, false) . ' />';
                    $output .= '<label class="onoffswitch-label" for="' . esc_attr($value['id']) . '"><span class="onoffswitch-inner"></span><span class="onoffswitch-switch"></span></label></div>';
                    $output .= '</div>';
                    $output .= '<span class="explain">' . wp_kses($explain_value, $allowedtags) . '</span>';
                    break;

                // Multicheck
                case "multicheck":
                    foreach ($value['options'] as $key => $option) {
                        $checked = '';
                        $label = $option;
                        $option = preg_replace('/[^a-zA-Z0-9._\-]/', '', strtolower($key));

                        $id = $option_name . '-' . $value['id'] . '-' . $option;
                        $name = $option_name . '[' . $value['id'] . '][' . $option . ']';

                        if (isset($val[$option])) {
                            $checked = checked($val[$option], 1, false);
                        }

                        $output .= '<div class="check-wrapper"><input id="' . esc_attr($id) . '" class="checkbox of-input" type="checkbox" name="' . esc_attr($name) . '" ' . $checked . ' /><label for="' . esc_attr($id) . '">' . esc_html($label) . '</label></div>';
                    }
                    break;

                // Color picker
                case "color":
                    $default_color = '';
                    if (isset($value['std'])) {
                        if ($val != $value['std']) {
                            $default_color = ' data-default-color="' . $value['std'] . '" ';
                        }
                    }
                    $output .= '<input name="' . esc_attr($option_name . '[' . $value['id'] . ']') . '" id="' . esc_attr($value['id']) . '" class="of-color"  type="text" value="' . esc_attr($val) . '"' . $default_color . ' />';

                    break;

                // Uploader
                case "upload":
                    $output .= Options_Framework_Media_Uploader::optionsframework_uploader($value['id'], $val, null);

                    break;

                // Background
                case 'background':

                    $background = $val;

                    // Background Color
                    $default_color = '';
                    if (isset($value['std']['color'])) {
                        if ($val != $value['std']['color']) {
                            $default_color = ' data-default-color="' . $value['std']['color'] . '" ';
                        }
                    }
                    $output .= '<input name="' . esc_attr($option_name . '[' . $value['id'] . '][color]') . '" id="' . esc_attr($value['id'] . '_color') . '" class="of-color of-background-color"  type="text" value="' . esc_attr($background['color']) . '"' . $default_color . ' />';

                    // Background Image
                    if (!isset($background['image'])) {
                        $background['image'] = '';
                    }

                    $output .= Options_Framework_Media_Uploader::optionsframework_uploader($value['id'], $background['image'], null, esc_attr($option_name . '[' . $value['id'] . '][image]'));

                    $class = 'of-background-properties';
                    if ('' == $background['image']) {
                        $class .= ' hide';
                    }
                    $output .= '<div class="' . esc_attr($class) . '">';

                    // Background Repeat
                    $output .= '<select class="of-background of-background-repeat" name="' . esc_attr($option_name . '[' . $value['id'] . '][repeat]') . '" id="' . esc_attr($value['id'] . '_repeat') . '">';
                    $repeats = of_recognized_background_repeat();

                    foreach ($repeats as $key => $repeat) {
                        $output .= '<option value="' . esc_attr($key) . '" ' . selected($background['repeat'], $key, false) . '>' . esc_html($repeat) . '</option>';
                    }
                    $output .= '</select>';

                    // Background Position
                    $output .= '<select class="of-background of-background-position" name="' . esc_attr($option_name . '[' . $value['id'] . '][position]') . '" id="' . esc_attr($value['id'] . '_position') . '">';
                    $positions = of_recognized_background_position();

                    foreach ($positions as $key => $position) {
                        $output .= '<option value="' . esc_attr($key) . '" ' . selected($background['position'], $key, false) . '>' . esc_html($position) . '</option>';
                    }
                    $output .= '</select>';

                    // Background Attachment
                    $output .= '<select class="of-background of-background-attachment" name="' . esc_attr($option_name . '[' . $value['id'] . '][attachment]') . '" id="' . esc_attr($value['id'] . '_attachment') . '">';
                    $attachments = of_recognized_background_attachment();

                    foreach ($attachments as $key => $attachment) {
                        $output .= '<option value="' . esc_attr($key) . '" ' . selected($background['attachment'], $key, false) . '>' . esc_html($attachment) . '</option>';
                    }
                    $output .= '</select>';
                    $output .= '</div>';

                    break;

                // Editor
                case 'editor':
                    $output .= '<div class="explain">' . wp_kses($explain_value, $allowedtags) . '</div>' . "\n";
                    echo $output;
                    $textarea_name = esc_attr($option_name . '[' . $value['id'] . ']');
                    $default_editor_settings = array(
                        'textarea_name' => $textarea_name,
                        'media_buttons' => false,
                        'tinymce' => array('plugins' => 'wordpress')
                    );
                    $editor_settings = array();
                    if (isset($value['settings'])) {
                        $editor_settings = $value['settings'];
                    }
                    $editor_settings = array_merge($default_editor_settings, $editor_settings);
                    wp_editor($val, $value['id'], $editor_settings);
                    $output = '';
                    break;

                // Info
                case "info":
                    $id = '';
                    $class = 'section';
                    if (isset($value['id'])) {
                        $id = 'id="' . esc_attr($value['id']) . '" ';
                    }
                    if (isset($value['type'])) {
                        $class .= ' section-' . $value['type'];
                    }
                    if (isset($value['class'])) {
                        $class .= ' ' . $value['class'];
                    }

                    $output .= '<div ' . $id . 'class="' . esc_attr($class) . '">' . "\n";
                    if (isset($value['name'])) {
                        $output .= '<h4 class="heading">' . esc_html($value['name']) . '</h4>' . "\n";
                    }
                    if ($value['desc']) {
                        $output .= apply_filters('of_sanitize_info', $value['desc']) . "\n";
                    }
                    $output .= '</div>' . "\n";
                    break;

                // Heading for Navigation
                case "heading":
                    $counter++;
                    if ($counter >= 2) {
                        $output .= '</div>' . "\n";
                    }
                    $class = '';
                    $class = !empty($value['id']) ? $value['id'] : $value['name'];
                    $class = preg_replace('/[^a-zA-Z0-9._\-]/', '', strtolower($class));
                    $output .= '<div id="options-group-' . $counter . '" class="group ' . $class . '">';
                    $output .= '<h3>' . esc_html($value['name']) . '</h3>' . "\n";
                    break;
            }

            if (($value['type'] != "heading") && ($value['type'] != "info")) {
                $output .= '</div>';
                $output .= '</div></div>' . "\n";
            }

            echo $output;
        }

        // Outputs closing div if there tabs
        if (Options_Framework_Interface::optionsframework_tabs() != '') {
            echo '</div>';
        }
    }

    /**
     * Generates the tabs that are used in the options menu
     */
    static function optionsframework_tabs() {
        $counter = 0;
        $options = & Waboot_Options_Framework::_optionsframework_options();
        $menu = '';

        foreach ( $options as $value ) {
            // Heading for Navigation
            if ( $value['type'] == "heading" && (!isset($value['component'])) ) {
                $counter++;
                $class = '';
                $class = ! empty( $value['id'] ) ? $value['id'] : $value['name'];
                $class = preg_replace( '/[^a-zA-Z0-9._\-]/', '', strtolower($class) ) . '-tab';
                $menu .= '<li><a id="options-group-'.  $counter . '-tab" class="nav-tab ' . $class .'" title="' . esc_attr( $value['name'] ) . '" href="' . esc_attr( '#options-group-'.  $counter ) . '">' . esc_html( $value['name'] ) . '</a></li>';
            }
        }

        return $menu;
    }
}