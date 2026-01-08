<?php

namespace Waboot\inc;

/**
 * Executes <head> actions
 */
function site_head(){
    $useCustomHead = apply_filters('waboot/head/use_custom_head', false);
    if($useCustomHead){
        renderCustomHead();
    }else{
        do_action('waboot/head/start');
        wp_head();
        do_action('waboot/head/end');
    }
}

function widgetArea(string $areaId){
    do_action('waboot/widget_area/before');
    do_action("waboot/widget_area/{$areaId}/before");
    dynamic_sidebar($areaId);
    do_action("waboot/widget_area/{$areaId}/after");
    do_action('waboot/widget_area/after');
}

/**
 * Return the $title wrapped between $prefix and $suffix.
 *
 * @param $prefix
 * @param $suffix
 * @param $title
 * @param \WP_Post|null $post
 */
function wrappedTitle($prefix,$suffix,$title,\WP_Post|null $post = null): void {
    global $wp_query;
    if(!$post) global $post;
    $prefix = apply_filters('waboot/main/title/prefix',$prefix,$post, $wp_query);
    $suffix = apply_filters('waboot/main/title/suffix',$suffix,$post, $wp_query);
    echo $prefix.$title.$suffix;
}

/**
 * A version of the_excerpt() that applies the trim function to the predefined excerpt as well
 *
 * @param int|null $length
 * @param string|null $more
 * @param int|null $post_id
 * @param bool $fallback_to_content use the post content if the excerpt is empty
 */
function trimmedExcerpt(int|null $length = null, string|null $more = null, int|null $post_id = null, bool $fallback_to_content = false): void {
    echo getTrimmedExcerpt($length,$more,$post_id,$fallback_to_content);
}

/**
 * Prints the desktop logo
 *
 * @param bool $linked
 * @param string $class
 */
function theLogo(bool $linked = false, string $class = ''): void {
    if($linked){
        $tpl = '<a href="%s"><img src="%s" class="'.$class.'" /></a>';
        printf($tpl,home_url( '/' ),getLogo());
    }else{
        $tpl = '<img src="%s" class="'.$class.'" />';
        printf($tpl,getLogo());
    }
}
