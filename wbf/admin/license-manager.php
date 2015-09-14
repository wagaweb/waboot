<?php

namespace WBF\admin;

use WBF\includes\License;

class License_Manager{

	static function admin_license_menu_item($parent_slug){
		$licenses = self::get();
		if(is_array($licenses) || !empty($licenses)){
			add_submenu_page( $parent_slug, __( "Waboot License", "wbf" ), __( "License", "wbf" ), "edit_theme_options", "wbf_license", "WBF\admin\License_Manager::license_page" );
		}
	}

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

		$current_license = get_option("waboot_license","");
		$crypted_current_license = call_user_func(function($cut_point = 4) use($current_license){
			$first_chars = substr($current_license,0,strlen($current_license)-$cut_point);
			$first_chars = preg_replace("|[\\w]|","*",$first_chars);
			$last_chars = substr($current_license,strlen($current_license)-$cut_point);
			return $first_chars.$last_chars;
		});
		$status = self::get_license_status();

		?>
		<div class="wrap">
			<h2><?php _e( "Waboot License", "wbf" ); ?></h2>
			<p>
			<form method="post" action="admin.php?page=waboot_license" >
				<p><?php _e("Here you can enter your license:", "wbf"); ?></p>
				<?php foreach($licenses as $slug => $license): ?>

				<?php endforeach; ?>
				<input type="text" value="<?php echo $crypted_current_license; ?>" name="license_code" />
				<p class="submit">
					<input type="submit" name="submit-license" id="submit" class="button button-primary" value="Validate License" <?php if($status == "Active") echo "disabled"; ?>>
					<input type="submit" name="delete-license" id="delete" class="button button-primary" value="Delete License">
				</p>
				<div id="license-status">
					<p>Current License Status: <?php self::print_license_status($status); ?></p>
				</div>
				<?php wp_nonce_field('submit_licence_nonce','license_nonce_field'); ?>
			</form>
			</p>
			<?php \WBF::print_copyright(); ?>
		</div>
	<?php
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

	static function get(){
		return apply_filters("wbf/admin/licences/registered",[]);
	}
}