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

add_action("waboot/widget_area/footer/before", function(){
    echo '<div class="widgetarea__footer-inner">';
});

add_action("waboot/widget_area/footer/after", function(){
    echo '</div>';
});

add_action("waboot/layout/title/before", function(){
    if ( function_exists('yoast_breadcrumb') ) {
        yoast_breadcrumb( '<p id="breadcrumbs">','</p>' );
    }
});

/*
 * Hide Title in Specific Page
 */
add_filter('waboot/main/title/display_flag', function($can_display_title){
    if(is_front_page()){
        return false;
    }
    return $can_display_title;
},5,3);
