<?php

trait WBFInstaller {
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
}