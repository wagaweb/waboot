<?php

require_once 'WabootGenerator.php';

class BootstrapGenerator extends WabootGenerator {
	const DOWNLOAD_URL = 'http://update.waboot.org/resource/get/plugin/wbf';

	/**
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
	 * @return bool
	 */
	public function install_wbf(){
		$zipfile = WP_PLUGIN_DIR.'/wbf.zip';
		if(!is_file($zipfile)) return false;

		WP_Filesystem();

		$r = unzip_file($zipfile,WP_PLUGIN_DIR);
		if(is_wp_error($r)){
			return false;
		}

		$this->activate_wbf();

		return true;
	}
}