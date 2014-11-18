<?php

/**
 * @package   Options_Framework
 * @author    Riccardo D'Angelo <me@riccardodangelo.com>
 */
class Options_Framework_Waboot_Code_Editor {
	static function optionsframework_codeditor( $_id, $_value, $_desc = '', $_name = '', $_lang = 'css' ) {
		$optionsframework_settings = get_option( 'optionsframework' );

		// Gets the unique option id
		$option_name = $optionsframework_settings['id'];

		$output = '';
		$id     = '';
		$class  = '';
		$int    = '';
		$value  = '';
		$name   = '';

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

		$class = "of-input codemirror";

		$output .= "<textarea id='$id' class='$class' name='$name' data-lang='$_lang' rows='8'>$value</textarea>";

		/*$output .= "<script>
		var editor = CodeMirror.fromTextArea(document.getElementById('{$id}'), {
		  mode: 'css',
		  lineNumbers: true
		});
		</script>";*/

		return $output;
	}

	public function init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'optionsframework_codeditor_scripts' ) );
		add_action( 'updated_option', array( $this, 'optionsframework_codeditor_save' ), 10, 3 );
	}

	function optionsframework_codeditor_scripts( $hook ) {
		$menu = Options_Framework_Admin::menu_settings();

		if ( 'waboot_page_' . $menu['menu_slug'] != $hook ) {
			return;
		}

		wp_register_script( 'codemirror', OPTIONS_FRAMEWORK_DIRECTORY . 'includes/vendor/codemirror/lib/codemirror.js' );
		wp_register_style( 'codemirror-css', OPTIONS_FRAMEWORK_DIRECTORY . 'includes/vendor/codemirror/lib/codemirror.css' );
		wp_register_script( 'of-waboot-codeditor', OPTIONS_FRAMEWORK_DIRECTORY . 'js/code-editor.js', array(
				'jquery',
			'codemirror',
			'underscore'
			), Options_Framework::VERSION );

		//Modes
		wp_register_script( 'codemirror-mode-css', OPTIONS_FRAMEWORK_DIRECTORY . 'includes/vendor/codemirror/mode/css/css.js', array( 'codemirror' ) );

		//Addons
		wp_register_script( 'codemirror-addon-hint', OPTIONS_FRAMEWORK_DIRECTORY . 'includes/vendor/codemirror/addon/hint/show-hint.js' );
		wp_register_style( 'codemirror-addon-hint-style', OPTIONS_FRAMEWORK_DIRECTORY . 'includes/vendor/codemirror/addon/hint/show-hint.css' );
		wp_register_script( 'codemirror-addon-hint-css', OPTIONS_FRAMEWORK_DIRECTORY . 'includes/vendor/codemirror/addon/hint/css-hint.js', array(
				'codemirror',
				'codemirror-addon-hint'
			) );

		//Themes
		wp_register_style( 'codemirror-theme-ambiance', OPTIONS_FRAMEWORK_DIRECTORY . 'includes/vendor/codemirror/theme/ambiance.css' );

		/**
		 * Enqueues
		 */
		wp_enqueue_script( 'codemirror' );
		wp_enqueue_style( 'codemirror-css' );

		wp_enqueue_script( 'codemirror-mode-css' );
		wp_enqueue_script( 'codemirror-addon-hint' );
		wp_enqueue_style( 'codemirror-addon-hint-style' );
		wp_enqueue_script( 'codemirror-addon-hint-css' );
		wp_enqueue_style( 'codemirror-theme-ambiance' );

		wp_enqueue_script( 'of-waboot-codeditor' );
	}

	function optionsframework_codeditor_save( $option, $old_value, $value ) {
		if ( array_key_exists( "waboot_custom_css", $value ) ) {
			$content = $value['waboot_custom_css'];
			$filename = "client-custom.css"; //todo: make file name customizable
			$filepath = get_stylesheet_directory() . "/assets/css/" . $filename;
			file_put_contents( $filepath, $content );
		}
	}
}