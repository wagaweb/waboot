<?php

namespace WBF\includes\compiler;
use \WBF\includes\compiler\less\Less_Compiler;
use \Exception;
use \WP_Error;

class Styles_Compiler{
	var $base_compiler;

	function __construct($compile_sets,$base_compiler = "less"){
		switch($base_compiler){
			case "less":
			default:
				require_once "less/Less_Compiler.php";
				$this->base_compiler = new Less_Compiler($compile_sets);
				break;
		}

		$this->maybe_release_lock();

		if (isset($_GET['compile']) && $_GET['compile'] == true) {
			if (current_user_can('manage_options')) {
				$this->compile();
			}
		}

		if (isset($_GET['clear_cache'])) {
			if (current_user_can('manage_options')) {
				$this->clear_cache();
				$this->compile();
			}
		}
	}

	function compile(){
		/** This filter is documented in wp-admin/admin.php */
		@ini_set( 'memory_limit', apply_filters( 'admin_memory_limit', WP_MAX_MEMORY_LIMIT ) );
		try{
			if(!$this->can_compile()) throw new CompilerBusyException();
			update_option('waboot_compiling_flag',1) or add_option('waboot_compiling_flag',1,'',true); //lock the compiler
			update_option('waboot_compiling_last_attempt',time()) or add_option('waboot_compiling_last_attempt',time(),'',true); //keep note of the current time
			$this->base_compiler->compile(); //COMPILE with specified compiler!
			update_option('waboot_compiling_flag',0); //release the compiler
			if ( current_user_can( 'manage_options' ) ) {
				if(is_admin()){
					if(isset($GLOBALS['option_page']) && $GLOBALS['option_page'] == 'optionsframework'){
						add_settings_error('options-framework', 'save_options', __('Less files compiled successfully.', 'wbf'), 'updated fade');
					}else{
						add_action( 'admin_notices', '\WBF\includes\compiler\compiled_admin_notice');
					}
				}else{
					echo '<div class="alert alert-success"><p>'.__('Theme styles files compiled successfully.', 'wbf').'</p></div>';
				}
			}
		}catch(Exception $e){
			if(!$e instanceof CompilerBusyException) update_option('waboot_compiling_flag',0); //release the compiler
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

	function clear_cache(){
		update_option('waboot_compiling_flag',0); //release the compiler

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
		$busyflag = get_option("waboot_compiling_flag",0);
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
			$last_attempt = get_option("waboot_compiling_last_attempt");
			if(!$last_attempt){
				update_option('waboot_compiling_flag',0); //release the compiler just to be sure
			}else{
				$current_time = time();
				$time_diff = ($current_time - $last_attempt)/60;
				if($time_diff > $timelimit){ //2 minutes
					update_option('waboot_compiling_flag',0); //release the compiler
				}
			}
		}
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