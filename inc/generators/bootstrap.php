<?php

class BootstrapGenerator extends WabootGenerator {
	const DOWNLOAD_URL = 'http://update.waboot.org/resource/get/plugin/wbf';

	/**
	 * @return bool
	 * @throws Exception
	 */
	public function download_wbf(){
		if($this->is_wbf_installed()) return true;

		$this->clear_wbf_directory();

		$dest = WP_CONTENT_DIR;
		$r = file_put_contents($dest."/wbf.zip",fopen(self::DOWNLOAD_URL,'r'));
		if(!$r){
			throw new \Exception("Unable to download WBF");
		}
		return true;
	}

	/**
	 * @return bool
	 */
	public function install_wbf(){
		return true;
	}
}