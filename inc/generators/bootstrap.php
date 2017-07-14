<?php

require_once 'WabootGenerator.php';

class BootstrapGenerator extends WabootGenerator {
	const DOWNLOAD_URL = 'http://update.waboot.org/resource/get/plugin/wbf';

	/**
	 * Download WBF
	 *
	 * @pre_action
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function download_wbf(){
		if($this->is_wbf_installed()) return true;

		$this->clear_wbf_directory();

		$zipfile = WP_PLUGIN_DIR."/wbf.zip";
		if(is_file($zipfile)){
			unlink($zipfile);
		}

		$r = file_put_contents($zipfile,fopen(self::DOWNLOAD_URL,'r'));
		if(!$r){
			throw new \Exception("Unable to download WBF");
		}
		return true;
	}

	/**
	 * Install WBF
	 *
	 * @pre_action
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function install_wbf(){
		$zipfile = WP_PLUGIN_DIR.'/wbf.zip';
		if(!is_file($zipfile)) return false;
		if($this->is_wbf_active()) return true;

		WP_Filesystem();

		$r = unzip_file($zipfile,WP_PLUGIN_DIR);
		if(is_wp_error($r)){
			unlink($zipfile);
			throw new \Exception($r->get_error_message());
		}

		unlink($zipfile);

		$this->activate_wbf();

		return true;
	}

	/**
	 * Create a new post to say hello to every one!
	 *
	 * @action
	 */
	public function sayHello(){
		$hello_post = get_posts([
			'name'        => 'hello-waboot',
			'post_type'   => 'post',
			'post_status' => 'publish',
			'numberposts' => 1
		]);
		if(!$hello_post || empty($hello_post)){
			wp_insert_post([
				'post_name' => 'hello-waboot',
				'post_title' => 'Hello Waboot!',
				'post_content' => 'We want to thank you for using Waboot. Enjoy and create amazing things!'
			]);
		}
	}
}