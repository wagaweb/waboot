<?php

namespace Waboot\inc;

use Waboot\inc\cli\PublishMissingArticles;
use function Waboot\inc\core\helpers\registerCommand;

add_action('init', static function(){
	//test commands here
});

if (!defined('WP_CLI')) {
	return;
}

try{
	/*
	 * Add commands here
	 */
	registerCommand('publish-missed-posts', PublishMissingArticles::class);
}catch (\Exception $e){}