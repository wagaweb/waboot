<?php

namespace Waboot\inc\hooks;

use function Waboot\inc\core\Waboot;

function displayHeader(){
    Waboot()->renderView('templates/view-parts/main-header.php');
}
add_action('waboot/layout/header', __NAMESPACE__."\\displayHeader");

function displayFooter(){
    Waboot()->renderView('templates/view-parts/main-footer.php');
}
add_action('waboot/layout/footer', __NAMESPACE__."\\displayFooter");
