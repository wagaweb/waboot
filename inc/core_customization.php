<?php

/**
 * Global core customization
 */

function waboot_title_toggler($title){
    if(!get_behavior("show-title")){
        return "";
    }
    return $title;
}
add_filter("alienship_entry_title_text","waboot_title_toggler");