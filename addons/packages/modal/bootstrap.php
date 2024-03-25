<?php
	
namespace Waboot\addons\packages\modal;

use Waboot\inc\core\mvc\HTMLView;
use function Waboot\addons\getAddonDirectory;

add_action('waboot/layout/page-after', function(){
	$v = new HTMLView(getAddonDirectory('modal').'/templates/modalTemplate.php',false);
	$v->clean()->display();
});