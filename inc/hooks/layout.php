<?php

namespace Waboot\inc\hooks;

use function Waboot\inc\core\Waboot;

add_action('waboot/layout/header', function(){
    Waboot()->renderView('templates/view-parts/main-header.php');
});

add_action('waboot/layout/footer', function(){
    Waboot()->renderView('templates/view-parts/main-footer.php');
});

/*
 * Hide Title in Specific Page
 *
add_filter('waboot/main/title/display_flag', function($can_display_title){
    if(is_front_page()){
        return false;
    }
    return $can_display_title;
},5,3);
*/
