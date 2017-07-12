<?php

namespace Waboot;

use WBF\components\notices\conditions\Condition;

/**
 * Class DoneWizardCondition
 *
 * It is used to determine if the wizard has been done or not.
 *
 * @package Waboot
 */
class DoneWizardCondition implements Condition{
	function verify() {
		if(isset($_GET['page']) && $_GET['page'] == "waboot_setup_wizard"){
			return true;
		}
		return Theme::is_wizard_done();
	}
}