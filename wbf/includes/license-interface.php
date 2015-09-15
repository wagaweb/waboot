<?php

namespace WBF\includes;

interface License_Interface {
	function check_license($args);
	function sanitize_license($license_code);
	function get_license_status();
	function print_license_status();
}