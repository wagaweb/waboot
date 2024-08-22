<?php

namespace Waboot\inc;

use Waboot\inc\cli\GenerateSiteStatFile;
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
	registerCommand('generate-site-stat-file', GenerateSiteStatFile::class);
}catch (\Exception $e){}