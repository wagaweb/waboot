<?php

require_once 'WBFInstaller.php';
require_once 'WabootGenerator.php';

class LayoutOneGenerator extends WabootGenerator {
	use WBFInstaller;

	const DOWNLOAD_URL = 'http://update.waboot.org/resource/get/plugin/wbf';

	/**
	 * Create a new post to say hello to every one!
	 *
	 * @action
	 */
	public function sayHello(){
		$hello_post = get_posts([
			'name'        => 'hello-waboot',
			'post_type'   => 'post',
			'post_status' => 'draft',
			'numberposts' => 1
		]);
		if(!$hello_post || empty($hello_post)){
			wp_insert_post([
				'post_name' => 'hello-waboot',
				'post_title' => 'Hello Waboot!',
				'post_content' => 'We want to thank you for using Waboot. Enjoy and create amazing things!',
				'post_status' => 'draft'
			]);
		}
	}
}