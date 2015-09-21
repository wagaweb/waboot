<?php

namespace WBF\admin;

use WBF\includes\License;
use WBF\includes\License_Exception;
use WBF\includes\Theme_Update_Checker;

class License_Manager{

	static function init(){
		add_action( 'current_screen', 'WBF\admin\License_Manager::perform_page_actions', 10 );
		add_action( 'wbf_admin_submenu', 'WBF\admin\License_Manager::admin_license_menu_item', 30 );
	}

	static function perform_page_actions(){
		global $wbf_notice_manager;
		$screen = get_current_screen();
		if(isset($_POST['update-license'])){
			try{
				if(!isset($_POST['slug'])) throw new License_Exception(__("License slug was not set","wbf"));
				if(!isset($_POST['type'])) throw new License_Exception(__("License type was not set","wbf"));
				if(!isset($_POST['code']) || empty($_POST['code'])) throw new License_Exception(__("License code was not set","wbf"));
				$slug = $_POST['slug'];
				$type = $_POST['type'];
				$code = trim($_POST['code']);
				self::update($slug,$type,$code);
				$wbf_notice_manager->add_notice("license_updated",_x("License successfully updated","License","wbf"),"updated","_flash_");
			}catch(License_Exception $e){
				$wbf_notice_manager->add_notice("license_not_updated",$e->getMessage(),"error","_flash_");
			}
		}elseif(isset($_POST['delete-license'])){
			try{
				if(!isset($_POST['slug'])) throw new License_Exception(__("License slug was not set","wbf"));
				if(!isset($_POST['type'])) throw new License_Exception(__("License type was not set","wbf"));
				$slug = $_POST['slug'];
				$type = $_POST['type'];
				self::delete($slug,$type);
				$wbf_notice_manager->add_notice("license_deleted",_x("License successfully deleted","License","wbf"),"updated","_flash_");
			}catch(License_Exception $e){
				$wbf_notice_manager->add_notice("license_not_deleted",$e->getMessage(),"error","_flash_");
			}
		}
	}

	static function admin_license_menu_item($parent_slug){
		$licenses = self::get_all();
		if(is_array($licenses) || !empty($licenses)){
			add_submenu_page( $parent_slug, __( "Licenses", "wbf" ), __( "Licenses", "wbf" ), "edit_theme_options", "wbf_licenses", "WBF\admin\License_Manager::license_page" );
		}
	}

	/**
	 * Callback for displaying the licenses page
	 */
	static function license_page(){
		?>
		<div class="wrap">
			<h2><?php _e( "Licenses", "wbf" ); ?></h2>
			<p><?php _e("Here you can enter your license.", "wbf"); ?></p>
			<?php if(self::has_theme_licenses()) : ?>
				<h3><?php _e("Theme license:", "wbf"); ?></h3>
				<?php foreach(self::get_theme_licenses() as $slug => $license): ?>
					<form method="post" action="admin.php?page=wbf_licenses">
						<?php
							$current_license = $license->get();
							if(!$current_license) $current_license = "";
							$status = $license->get_license_status();
						?>
						<div class="license">
							<h4><?php echo $license->nicename; ?></h4>
							<div class="license-body">
								<label><?php printf(_x("License code","License","wbf"),$license->nicename); ?>&nbsp;<input id="license_<?php echo $license->slug; ?>" type="text" value="<?php echo self::crypt_license_visual($current_license); ?>" name="code"/></label>
								<input type="submit" name="update-license" id="submit" class="button button-primary" value="<?php _ex("Update","License","wbf"); ?>" <?php if($license->is_valid()) echo "disabled"; ?>>
								<input type="submit" name="delete-license" id="delete" class="button button-primary" value="<?php _ex("Delete","License","wbf"); ?>">
								<div id="license-status">
									<p><?php _ex("Status:","License","wbf") ?>&nbsp;<?php $license->print_license_status(); ?></p>
								</div>
							</div>
						</div>
						<input type="hidden" name="slug" value="<?php echo $license->slug; ?>">
						<input type="hidden" name="type" value="theme">
					</form>
				<?php endforeach; ?>
			<?php endif; ?>
			<?php if(self::has_plugin_licenses()) : ?>
				<h3><?php _e("Plugin license:", "wbf"); ?></h3>
				<?php foreach(self::get_plugin_licenses() as $slug => $license): ?>
					<form method="post" action="admin.php?page=wbf_licenses">
						<?php
							$current_license = $license->get();
							$status = $license->get_license_status();
						?>
						<div class="license">
							<h4><?php echo $license->nicename; ?></h4>
							<div class="license-body">
								<label><?php _e("License code","wbf"); ?>&nbsp;<input type="text" value="<?php echo self::crypt_license_visual($current_license); ?>" name="code"/></label>
								<p class="submit">
									<input type="submit" name="update-license" id="submit" class="button button-primary" value="<?php _ex("Update","License","wbf"); ?>" <?php if($license->is_valid()) echo "disabled"; ?>>
									<input type="submit" name="delete-license" id="delete" class="button button-primary" value="<?php _ex("Delete","License","wbf"); ?>">
								</p>
								<div id="license-status">
									<p><?php _ex("Status:","License","wbf") ?>&nbsp;<?php $license->print_license_status(); ?></p>
								</div>
							</div>
						</div>
						<input type="hidden" name="slug" value="<?php echo $license->slug; ?>">
						<input type="hidden" name="type" value="plugin">
					</form>
				<?php endforeach; ?>
			<?php endif; ?>
			<?php wp_nonce_field('submit_licence_nonce','license_nonce_field'); ?>
			<?php \WBF::print_copyright(); ?>
		</div>
		<?php
	}

	/**
	 * Hides the first characters of a license code
	 * @param     $code
	 * @param int $cut_point
	 *
	 * @return string
	 */
	static function crypt_license_visual($code,$cut_point = 4){
		$first_chars = substr($code,0,strlen($code)-$cut_point);
		$first_chars = preg_replace("|[\\w]|","*",$first_chars);
		$last_chars = substr($code,strlen($code)-$cut_point);
		return $first_chars.$last_chars;
	}

	static function get($license_slug,$type){
		$licenses = self::get_all();
		if(array_key_exists($license_slug,$licenses[$type])){
			return $licenses[$type][$license_slug];
		}else{
			return false;
		}
	}

	/**
	 * Returns all theme licences, or false
	 * @return array|bool
	 */
	static function get_theme_licenses(){
		$licenses = self::get_all();
		if(isset($licenses['theme'])){
			return $licenses['theme'];
		}else{
			return false;
		}
	}

	/**
	 * Checks if there are at least one registered theme license
	 * @return bool
	 */
	static function has_theme_licenses(){
		return is_array(self::get_theme_licenses());
	}

	/**
	 * Checks if there are at least one registered plugin license
	 * @return bool
	 */
	static function has_plugin_licenses(){
		return is_array(self::get_plugin_licenses());
	}

	/**
	 * Returns all plugin licenses or false
	 * @return array|bool
	 */
	static function get_plugin_licenses(){
		$licenses = self::get_all();
		if(isset($licenses['plugin'])){
			return $licenses['plugin'];
		}else{
			return false;
		}
	}

	/**
	 * Update a specific license
	 * @param $license_slug
	 * @param $type
	 * @param $value
	 */
	static function update($license_slug,$type,$value){
		$l = self::get($license_slug,$type);
		$value = $l::sanitize_license($value);
		if($value && is_string($value)){
			$l->update($value);
		}else{
			throw new License_Exception(__("License sanitization has gone wrong","wbf"));
		}
	}

	/**
	 * Delete a specific license
	 * @param $license_slug
	 * @param $type
	 */
	static function delete($license_slug,$type){
		$l = self::get($license_slug,$type);
		$l->remove();
	}

	/**
	 * Returns registered licenses
	 * @return mixed|void
	 */
	static function get_all(){
		$licenses = apply_filters("wbf/admin/licences/registered",[]);
		return $licenses;
	}

	/**
	 * Register a new license
	 * @param \WBF\includes\License $license
	 * @return License
	 */
	static function register(License $license, $type){
		add_filter("wbf/admin/licences/registered",function($licenses) use($license, $type){
			$licenses[$type][$license->slug] = $license;
			return $licenses;
		});
		return $license;
	}

	/**
	 * Register a License class for a theme
	 * @param License $license
	 */
	static function register_theme_license(License $license){
		$license = self::register($license,"theme");
		/**
		 * Set update server
		 */
		if(class_exists('\WBF\includes\Theme_Update_Checker')){
			$GLOBALS['WBFThemeUpdateChecker'] = new Theme_Update_Checker(
				$license->slug,
				$license->metadata_call
			);
		}
	}

	/**
	 * Register a License class for a plugin. The call to Plugin_Update_Checker is done by Plugin Framework
	 * @param License $license
	 *
	 * @return License
	 */
	static function register_plugin_license(License $license){
		return self::register($license,"plugin");
	}

	/**
	 * Checks if a specified theme has a license registered
	 * @param $theme_slug
	 *
	 * @return \WBF\includes\License|bool
	 */
	static function theme_has_license($theme_slug){
		return self::get($theme_slug,"theme");
	}
}