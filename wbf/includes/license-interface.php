<?php

namespace WBF\includes;

interface License_Interface {
	function sanitize_license($license);
	static function get_license_status();
	static function check_license($licensekey, $localkey='');
} 