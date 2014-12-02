<?php
/**
 * Apply custom stylesheet to admin panel
 *
 * @param $page
 * @since 0.1.0
 * @uses waboot_locate_template_uri()
 */
function waboot_admin_styles($page) {
    wp_enqueue_style('waboot-admin-style', waboot_locate_template_uri('wbf/admin/css/admin.css'), array(), '1.0.0', 'all');
}

add_action('admin_enqueue_scripts', 'waboot_admin_styles');

/**
 * Apply custom stylesheet to the wordpress visual editor.
 *
 * @since 0.1.0
 * @uses add_editor_style()
 * @uses get_stylesheet_uri()
 */
function waboot_editor_styles() {
    $theme_name = apply_filters("waboot_compiled_stylesheet_name", wp_get_theme()->stylesheet);

    add_editor_style(waboot_locate_template_uri("assets/css/{$theme_name}.css"));
    add_editor_style('wbf/admin/css/tinymce.css');
}

add_action('init', 'waboot_editor_styles');

/**
 * Apply "post-type relative" custom stylesheet to visual editor
 * @since 0.1.0
 * @uses add_editor_style()
 * @uses get_post_type()
 */
function waboot_post_type_editor_styles() {
    global $post;
    if (isset($post->ID)) {
        $post_type = get_post_type($post->ID);
        $editor_style = 'tinymce-' . $post_type . '.css'; //Es: tinymce-post.css
        add_editor_style("wbf/admin/css/" . $editor_style);
    }
}

add_action('pre_get_posts', 'waboot_post_type_editor_styles');