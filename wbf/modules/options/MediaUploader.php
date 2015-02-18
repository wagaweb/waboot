<?php

namespace WBF\modules\options;

class MediaUploader extends \Options_Framework_Media_Uploader
{
	/**
	 * Enqueue scripts for file uploader
	 */
	function optionsframework_media_scripts( $hook ) {

		$menu = Admin::menu_settings();

		if ( 'toplevel_page_' . $menu['menu_slug'] != $hook ) {
			return;
		}

		if ( function_exists( 'wp_enqueue_media' ) ) {
			wp_enqueue_media();
		}

		wp_register_script( 'of-media-uploader', OPTIONS_FRAMEWORK_DIRECTORY . 'js/media-uploader.js', array( 'jquery' ), Framework::VERSION );
		wp_enqueue_script( 'of-media-uploader' );
		wp_localize_script( 'of-media-uploader', 'optionsframework_l10n', array(
			'upload' => __( 'Upload', 'textdomain' ),
			'remove' => __( 'Remove', 'textdomain' )
		) );
	}
}