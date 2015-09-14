<?php

namespace WBF\admin;

use WBF\includes\License;

class License_Manager{

	static function admin_license_menu_item($parent_slug){
		$licenses = self::get();
		if(is_array($licenses) || !empty($licenses)){
			add_submenu_page( $parent_slug, __( "Licenses", "wbf" ), __( "Licenses", "wbf" ), "edit_theme_options", "wbf_licenses", "WBF\admin\License_Manager::license_page" );
		}
	}

	/**
	 * Callback for displaying the licenses page
	 */
	static function license_page(){
		$licenses = self::get();

		if(isset($_POST['submit-license'])){
			try{
				if(isset($_POST['license_code'])){
					if(isset( $_POST['license_nonce_field'] ) && wp_verify_nonce($_POST['license_nonce_field'],'submit_licence_nonce') ){
						$license = self::sanitize_license($_POST['license_code']);
						if($license){
							update_option("waboot_license",$license);
							?>
							<div class="updated">
								<p><?php _e( 'License Updated!', "wbf" ); ?></p>
							</div>
						<?php
						}else{
							throw new LicenseException(_( 'Unable to update the license!', "wbf" ));
						}
					}
				}
			}catch(LicenseException $e){
				?>
				<div class="updated">
					<p><?php echo $e->getMessage(); ?></p>
				</div>
			<?php
			}
		}

		if(isset($_POST['delete-license'])){
			update_option("waboot_license","");
		}

		?>
		<div class="wrap">
			<h2><?php _e( "Registered Licenses", "wbf" ); ?></h2>
			<p>
				<form method="post" action="admin.php?page=wbf_licenses">
					<p><?php _e("Here you can enter your license:", "wbf"); ?></p>
					<?php foreach($licenses as $slug => $license): ?>
						<?php
							$current_license = get_option("waboot_license","");
							$status = $license->get_license_status();
						?>
						<label><?php _e("License code","wbf"); ?>
							<input type="text" value="<?php echo self::crypt_license_visual($current_license); ?>" name="license_code"/>
						</label>
						<p class="submit">
							<input type="submit" name="submit-license" id="submit" class="button button-primary" value="Validate License" <?php if($status == "Active") echo "disabled"; ?>>
							<input type="submit" name="delete-license" id="delete" class="button button-primary" value="Delete License">
						</p>
						<div id="license-status">
							<p>Current License Status: <?php $license->print_license_status(); ?></p>
						</div>
					<?php endforeach; ?>
					<?php wp_nonce_field('submit_licence_nonce','license_nonce_field'); ?>
				</form>
			</p>
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

	/**
	 * Register a new license
	 * @param \WBF\includes\License $license
	 */
	static function register(License $license){
		add_filter("wbf/admin/licences/registered",function($licenses) use($license){
			$licenses[$license->license_slug] = $licenses;
		});
	}

	/**
	 * Returns registered licenses
	 * @return mixed|void
	 */
	static function get(){
		$licenses = apply_filters("wbf/admin/licences/registered",[]);
		return $licenses;
	}
}