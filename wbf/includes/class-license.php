<?php

namespace WBF\includes;

class License{
	var $license_slug;
	var $license_type;
	var $license_option;
	var $license;
	var $metadata_call = false;

	public static function getInstance($license_slug = null) {
		static $instance = null;
		if (null === $instance) {
			if(isset($license_slug)){
				$instance = new static($license_slug);
			}else{
				throw new License_Exception(__("You must provide a license slug"));
			}
		}

		return $instance;
	}

	function __construct($license_slug,$args = []){
		$args = wp_parse_args($args,[
			'prefix' => true,
			'suffix' => false
		]);
		$this->license_slug = $license_slug;
		if($args['prefix']){
			$this->license_option = "wbf_license_{$this->license_slug}";
		}elseif($args['suffix']){
			$this->license_option = $this->license_slug."_license";
		}else{
			$this->license_option = $this->license_slug;
		}
		$this->license = $this->get();
	}

	function get(){
		return get_option($this->license_option,false);
	}

	function update($new_license){
		return update_option($this->license_option,$new_license);
	}

	function remove(){
		return delete_option($this->license_option);
	}
}