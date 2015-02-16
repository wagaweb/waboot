<?php
/**
 * Behaviors Framework
 *
 * @package   Behaviors Framework
 * @author    Riccardo D'Angelo <me@riccardodangelo.com>
 * @license   copyrighted
 * @link      http://www.waga.it
 * @copyright 2014 Riccardo D'Angelo and WAGA.it
 */

namespace WBF\modules\behaviors;

require_once "functions.php";

locate_template('/inc/behaviors.php', true);

add_action( 'add_meta_boxes', 'WBF\modules\behaviors\create_metabox' );

add_action( 'save_post', 'WBF\modules\behaviors\save_metabox' );
add_action( 'pre_post_update', 'WBF\modules\behaviors\save_metabox' );
add_action( 'edit_post', 'WBF\modules\behaviors\save_metabox' );
add_action( 'publish_post', 'WBF\modules\behaviors\save_metabox' );
add_action( 'edit_page_form', 'WBF\modules\behaviors\save_metabox' );

//add_action( 'optionsframework_after_validate','waboot_reset_defaults' );

add_action("wbf_init",'WBF\modules\behaviors\module_init');
function module_init(){
	locate_template( '/wbf/admin/behaviors-framework.php', true );
	locate_template('/inc/behaviors.php', true);
}