<?php

namespace Waboot\inc\hooks;

use function Waboot\inc\core\Waboot;

add_action('waboot/layout/header', function(){
    Waboot()->renderView('templates/view-parts/main-header.php');
});

add_action('waboot/layout/footer', function(){
    Waboot()->renderView('templates/view-parts/main-footer.php');
});

add_action('waboot/layout/page-after', function(){
    Waboot()->renderView('templates/view-parts/navigation-mobile.php');
});

add_action('waboot/layout/page-after', function(){
    Waboot()->renderView('templates/view-parts/sidesearch.php');
});

add_action("waboot/widget_area/footer/before", function(){
    echo '<div class="widgetarea__footer-inner">';
});

add_action("waboot/widget_area/footer/after", function(){
    echo '</div>';
});

/*
 * Hide Title in Specific Page
 */
add_filter('waboot/main/title/display_flag', function($can_display_title){
    if(
        is_front_page() ||
        get_field('hide_title')) {
        return false;
    }
    return $can_display_title;
},5,3);

/*
 * Specify performant <head> template
 */
add_filter('waboot/head/custom_head/tpl', static function($tpl){
    return 'templates/view-parts/performance-head.php';
});

/*
 * Renders performant <head> template
 */
/*add_filter('waboot/head/use_custom_head', static function($usePerformanceHead){
    if(\is_shop() || \is_product_taxonomy() || is_product_category()){
        return true;
    }
    return $usePerformanceHead;
});*/
