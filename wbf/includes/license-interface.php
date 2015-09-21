<?php

namespace WBF\includes;

interface License_Interface {
	/**
	 * Checks the license code under the proprietary algorithm
	 * @param $args
	 * @return mixed
	 */
	function check_license($args);
	/**
	 * Checks the license validity
	 * @return bool
	 */
	function is_valid();
	/**
	 * Sanitize the license code
	 * @param $license_code
	 * @return string
	 */
	static function sanitize_license($license_code);
	/**
	 * Get the license status. Must return "Active" if the provided license code is valid.
	 * @return string
	 */
	function get_license_status();
	/**
	 * Print out the license status
	 * @return string
	 */
	function print_license_status();
}