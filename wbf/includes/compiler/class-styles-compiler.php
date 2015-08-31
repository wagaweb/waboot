<?php

namespace WBF\includes\compiler;
use \Exception;
use \WP_Error;

class Styles_Compiler{
	var $base_compiler;

	function __construct($args,$base_compiler = null){
		if(!isset($base_compiler)){
			$base_compiler = [
				'require_path' => "less/Less_Compiler.php",
				'class_name' => '\WBF\includes\compiler\less\Less_Compiler'
			];
		}

		require_once $base_compiler['require_path'];
		$this->base_compiler = new $base_compiler['class_name']($args);

		$this->maybe_release_lock();

		if (isset($_GET['compile']) && $_GET['compile'] == true) {
			if (current_user_can('manage_options')) {
				$this->compile();
			}
		}

		if (isset($_GET['clear_cache'])) {
			if (current_user_can('manage_options')) {
				do_action("wbf/compiler/cache/pre_clean");
				$this->clear_cache();
				do_action("wbf/compiler/cache/post_clean");
				$this->compile();
			}
		}
	}

	function compile($setname = false){
		/** This filter is documented in wp-admin/admin.php */
		@ini_set( 'memory_limit', apply_filters( 'admin_memory_limit', WP_MAX_MEMORY_LIMIT ) );
		try{
			if(!$this->can_compile()) throw new CompilerBusyException();
			$args = $setname && !empty($setname) ? $this->get_compile_sets()[$setname] : false; //The set args

			$this->lock(); //lock the compiler
			$this->update_last_compile_attempt($setname); //keep note of the current time

			$return_css_flag = true;
			do_action("wbf/compiler/pre_compile");
			if($setname && is_string($setname)){
				/*
				 * SPECIFIC SET COMPILING
				 */
				do_action("wbf/compiler/pre_compile/{$setname}",$args);
				if(isset($args['compile_pre_callback'])){
					call_user_func($args['compile_pre_callback'],$args);
				}
				$css = $this->base_compiler->compile($setname); //COMPILE with specified compiler!
				if(isset($args['output']) && !empty($args['output'])){
					$this->write_to_file($css,$args['output']);
					$return_css_flag = false;
				}
				do_action("wbf/compiler/post_compile/{$setname}",$args,$css);
				if(isset($args['compile_post_callback'])){
					call_user_func($args['compile_post_callback'],$css);
				}
			}else{
				/*
				 * GLOBAL COMPILING
				 */
				foreach($this->get_compile_sets() as $setname => $args){
					if($args['exclude_from_global_compile']) continue;
					do_action("wbf/compiler/pre_compile/{$setname}",$args);
					if(isset($args['compile_pre_callback'])){
						call_user_func($args['compile_pre_callback'],$args);
					}
					$css[$setname] = $this->base_compiler->compile($setname); //COMPILE with specified compiler!
					if(isset($args['output']) && !empty($args['output'])){
						$this->write_to_file($css[$setname],$args['output']);
						$return_css_flag = false;
					}
					do_action("wbf/compiler/post_compile/{$setname}",$args,$css[$setname]);
					if(isset($args['compile_post_callback'])){
						call_user_func($args['compile_post_callback'],$css[$setname]);
					}
				}
			}
			do_action("wbf/compiler/post_compile");

			$this->release_lock(); //release the compiler
			//Display end message:
			static $message_displayed = false;
			if ( current_user_can( 'manage_options' ) && !$message_displayed) {
				if(is_admin()){
					if(isset($GLOBALS['option_page']) && $GLOBALS['option_page'] == 'optionsframework'){
						add_settings_error('options-framework', 'save_options', __('Less files compiled successfully.', 'wbf'), 'updated fade');
					}else{
						add_action( 'admin_notices', '\WBF\includes\compiler\compiled_admin_notice');
					}
				}else{
					echo '<div class="alert alert-success"><p>'.__('Theme styles files compiled successfully.', 'wbf').'</p></div>';
				}
				$message_displayed = true;
			}
			if($return_css_flag && isset($css)){
				return $css;
			}else{
				return true;
			}
		}catch(Exception $e){
			if(!$e instanceof CompilerBusyException) $this->release_lock(); //release the compiler
			$wpe = new WP_Error( 'compile-failed', $e->getMessage() );
			if ( current_user_can( 'manage_options' ) ) {
				if(is_admin()){
					if(isset($GLOBALS['option_page']) && $GLOBALS['option_page'] == 'optionsframework'){
						add_settings_error('options-framework', 'save_options', $wpe->get_error_message(), 'error fade');
					}else{
						add_action( 'admin_notices', '\WBF\includes\compiler\compile_error_admin_notice');
					}
				}else{
					echo '<div class="alert alert-warning"><p>'.$wpe->get_error_message().'</p></div>';
				}
			}
		}
	}

	function write_to_file($css,$path){
		if(!is_file($path)){
			fclose(fopen($path,"w"));
		}

		if(!is_writable($path)){
			if(!chmod($path,0777))
				throw new Exception("Output dir ({$path}) is not writeable");
		}

		//$wp_filesystem->put_contents( $args['output'], $css, FS_CHMOD_FILE );
		file_put_contents($path, $css);
	}

	function clear_cache(){
		$this->release_lock(); //release the compiler

		foreach($this->base_compiler->compile_sets as $name => $args){
			$cachedir = $args['cache'];
			if(is_dir($cachedir)){
				$files = glob($cachedir."/*");
				foreach($files as $file){ // iterate files
					if(is_file($file))
						unlink($file); // delete file
				}
				if(is_admin()){
					add_action( 'admin_notices', '\WBF\includes\compiler\cache_cleared_admin_notice');
				}else{
					echo '<div class="alert alert-success"><p>'.__('Theme cache cleared successfully!', 'wbf').'</p></div>';
				}
			}
		}
	}

	function can_compile(){
		$busyflag = $this->get_lock_status();
		if($busyflag && $busyflag != 0){
			return false;
		}

		return true;
	}

	/**
	 * Releases the compiler lock if is passed too much time since last compilation attempt
	 * @param int $timelimit (in minutes)
	 */
	function maybe_release_lock($timelimit = 2){
		if(!$this->can_compile()){
			$last_attempt = $this->get_last_compile_attempt();
			if(!$last_attempt){
				$this->release_lock(); //release the compiler just to be sure
			}else{
				$current_time = time();
				$time_diff = ($current_time - $last_attempt)/60;
				if($time_diff > $timelimit){ //2 minutes
					$this->release_lock(); //release the compiler
				}
			}
		}
	}

	function lock(){
		update_option('waboot_compiling_flag',1) or add_option('waboot_compiling_flag',1,'',true);
	}

	function release_lock(){
		update_option('waboot_compiling_flag',0);
	}

	function get_lock_status(){
		return get_option("waboot_compiling_flag",0);
	}

	function update_last_compile_attempt($setname = false){
		$last_attempts = $this->get_last_compile_attempt($setname);
		if(!is_array($last_attempts)){
			$last_attempts = array();
		}
		$time = time();
		if($setname){
			$last_attempts[$setname] = $time;
		}else{
			foreach($this->get_compile_sets() as $name => $args){
				if($args['exclude_from_global_compile']) continue;
				$last_attempts[$name] = $time;
			}
		}
		$last_attempts['_global'] = $time;
		update_option('waboot_compiling_last_attempt',$last_attempts) or add_option('waboot_compiling_last_attempt',$last_attempts,'',true);
	}

	function get_last_compile_attempt($setname = false){
		$last_attempts = get_option('waboot_compiling_last_attempt');
		if(!$setname && isset($last_attempts['_global'])){
			return $last_attempts['_global'];
		}elseif(isset($last_attempts[$setname])){
			return $last_attempts[$setname];
		}
		return false;
	}

	/**
	 * Get the compile sets from current compiler. Return empty array if fails.
	 * @return array
	 */
	function get_compile_sets(){
		if(isset($this->base_compiler)){
			if(isset($this->base_compiler->compile_sets)){
				return $this->base_compiler->compile_sets;
			}
		}

		return array();
	}

	/**
	 * Get the primary compile set
	 * @return bool
	 */
	function get_primary_set(){
		$sets = $this->get_compile_sets();
		foreach($sets as $k => $s){
			if(isset($s['primary']) && $s['primary']){
				return $s;
			}
		}
		return false;
	}
}

class CompilerBusyException extends Exception{
	public function __construct($message = null, $code = 0, Exception $previous = null) {
		if(!isset($message)){
			$message = __("The compiler is busy","wbf");
		}
		parent::__construct($message, $code, $previous);
	}
}

/*function wbf_get_compiled_stylesheet_name(){
	return apply_filters("wbf_compiled_stylesheet_name",wp_get_theme()->stylesheet);
}*/

function compiled_admin_notice() {
	?>
	<div class="updated">
		<p><?php _e( 'Theme style files compiled successfully!', 'wbf' ); ?></p>
	</div>
	<?php
}

function compile_error_admin_notice() {
	?>
	<div class="error">
		<p><?php _e( 'Theme style files not compiled!', 'wbf' ); ?></p>
	</div>
	<?php
}

function cache_cleared_admin_notice() {
	?>
	<div class="updated">
		<p><?php _e( 'Theme cache cleared successfully!', 'wbf' ); ?></p>
	</div>
	<?php
}