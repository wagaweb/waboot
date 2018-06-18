<?php

namespace Waboot\exceptions;

class WBFNotFoundException extends \Exception{
	public function setup_wbf_installer(){
		add_action( 'admin_init' , function(){
			\Waboot\hooks\wbf_installer\install_wbf_wp_update_hooks();
		});
		if(!\Waboot\Theme::is_wizard_done() || !\Waboot\Theme::is_wizard_skipped()){
			\Waboot\hooks\wbf_installer\notice_install_requirements();
		}
	}
}