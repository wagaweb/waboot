<?php
/**
 * Apply custom stylesheet to admin panel
 *
 * @param $page
 *
*@since 0.1.0
 * @uses wbf_locate_template_uri()
 */
function waboot_admin_styles($page) {
    wp_enqueue_style('waboot-admin-style', wbf_locate_template_uri('wbf/admin/css/admin.css'), array(), '1.0.0', 'all');
}
add_action('admin_enqueue_scripts', 'waboot_admin_styles');

/**
 * Apply custom stylesheet to the wordpress visual editor.
 *
 * @since 0.1.0
 * @uses add_editor_style()
 */
function wbf_editor_styles() {
	add_editor_style('wbf/admin/css/tinymce.css');
}
add_action('admin_init', 'wbf_editor_styles');