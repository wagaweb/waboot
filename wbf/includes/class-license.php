<?php

namespace WBF\includes;

abstract class License{
	var $nicename = "License";
	var $slug;
	var $type;
	var $option_name;
	var $license;
	var $metadata_call = false;

	public static function getInstance($license_slug = null,$args = []) {
		static $instance = null;
		if (null === $instance) {
			if(isset($license_slug)){
				$instance = new static($license_slug,$args);
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
		$this->slug = $license_slug;
		if(!isset($this->option_name) || empty($this->option_name) || !is_string($this->option_name)){
			if($args['prefix']){
				$this->option_name = "wbf_license_{$this->slug}";
			}elseif($args['suffix']){
				$this->option_name = $this->slug."_license";
			}else{
				$this->option_name = $this->slug;
			}
		}
		$this->license = $this->get();
	}

	function get(){
		return get_option($this->option_name,false);
	}

	function update($new_license){
		return update_option($this->option_name,$new_license);
	}

	function remove(){
		return delete_option($this->option_name);
	}
}