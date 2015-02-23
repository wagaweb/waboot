<?php

namespace WBF\includes;

interface License_Interface {
	static function get_license_status();
	function sanitize_license($license);
	function check_license($licensekey, $localkey='');
} 