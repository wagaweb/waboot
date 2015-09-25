<?php

namespace WBF\modules\options;

class Admin extends \Options_Framework_Admin{

	public function init() {
		parent::init();
		remove_action( 'admin_menu', array( $this, 'add_options_page' ) );
		add_action( 'wbf_admin_submenu', array( $this, 'add_options_page' ) );
		add_action( 'wbf_admin_submenu', array( $this, 'add_man_page' ), 12 );
		//add_action( 'admin_menu', array( $this, 'add_additional_appearance_link' ) );
		add_action( 'optionsframework_after', array( $this, 'add_copy_in_admin_page' ));
	}

    /*function add_additional_appearance_link(){
        $menu = $this->menu_settings();
        $this->of_app_screen = add_theme_page($menu['page_title'],$menu['menu_title'],$menu['capability'],$menu['menu_slug']);
    }*/

	/**
	 * Add a subpage called "Theme Options" to the Waboot Menu
	 */
	function add_options_page() {
		$menu = $this->menu_settings();
		$this->options_screen = add_submenu_page( "waboot_options", $menu['page_title'], $menu['menu_title'], $menu['capability'], $menu['menu_slug'], array($this, 'options_page') );
	}

	/**
	 * Add "Manage Theme Options" subpage to WBF Menu
	 */
	public function add_man_page($parent_slug) {
		add_submenu_page( $parent_slug , __( "Theme Options Manager", "wbf" ), __( "Import/Export", "wbf" ), "edit_theme_options", "themeoptions-manager", array( $this, 'do_man_page') );
	}

	function add_copy_in_admin_page(){
		\WBF::print_copyright();
	}

	static function menu_settings() {
		$menu = array(
			'page_title' => __('Theme Options', 'wbf'),
			'menu_title' => __('Theme Options', 'wbf'),
			'capability' => 'edit_theme_options',
			'old_menu_slug' => 'options-framework',
			'menu_slug' => 'waboot_options'
		);
		return apply_filters('optionsframework_menu', $menu);
	}

	/**
	 * Adds options menu item to admin bar
	 */
	function optionsframework_admin_bar() {
		if( current_user_can('edit_theme_options') ){
			global $wp_admin_bar;
			$menu = $this->menu_settings();
			if(current_user_can($menu['capability'])){
				$wp_admin_bar->add_menu( array(
					'id' => 'of_theme_options',
					'title' => $menu['menu_title'],
					'parent' => 'appearance',
					'href' => admin_url( 'admin.php?page=' . $menu['menu_slug'] ),
					'meta' => [
						'title' => _x("Edit theme options","Admin bar","wbf")
					]
				));
			}
		}
	}

	/**
	 * Builds out the theme options manager page.
	 */
	public function do_man_page() {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		?>
		<div class="wrap">
			<?php
			if ( isset( $_POST['submit-backup'] ) ) {
				switch ( $_POST['option'] ) {
					case 'backup':
						try {
							$file = $this->_backup_options();
							wbf_admin_show_message( __( "Backup successfully created!", "wbf" ), "updated" );
						} catch ( \Exception $e ) {
							wbf_admin_show_message( $e->getMessage(), "error" );
						}
						break;
					default:
						wbf_admin_show_message( __( "Invalid option selected", "wbf" ), "error" );
						break;
				}
			}
			if ( isset( $_POST['submit-restore'] ) ) {
				if ( isset( $_FILES['remote-backup-file'] ) && $_FILES['remote-backup-file']['tmp_name'] != "" ) {
					$file = $_FILES['remote-backup-file'];
					if ( $file['error'] == UPLOAD_ERR_OK && is_uploaded_file( $file['tmp_name'] ) ) {
						try {
							$this->_restore_options_from_file( $file );
							wbf_admin_show_message( __( "Backup successfully restored!", "wbf" ), "updated" );
						} catch ( \Exception $e ) {
							wbf_admin_show_message( $e->getMessage(), "error" );
						}
					} else {
						wbf_admin_show_message( __( "Unable to upload the file.", "wbf" ), "error" );
					}
				} elseif ( isset( $_POST['local-backup-file'] ) ) {
					$file = $_POST['local-backup-file'];
					try {
						$this->_restore_options_from_file( $file );
						wbf_admin_show_message( __( "Backup successfully restored!", "wbf" ), "updated" );
					} catch ( \Exception $e ) {
						wbf_admin_show_message( $e->getMessage(), "error" );
					}
				} else {
					wbf_admin_show_message( __( "No backup file provided.", "wbf" ), "error" );
				}
			}
			$backup_files = $this->get_backupFiles();
			?>
			<h2><?php _e( "Theme Options Manager", "wbf" ); ?></h2>

			<h3><?php _e( "Export or Backup Theme Options", "wbf" ); ?></h3>

			<form action="admin.php?page=themeoptions-manager" method="POST" id="export-themeoptions">
				<p><label><input type="radio" name="option" value="backup"> <?php _e( "Backup current Theme Options on the disk", "wbf" ); ?></label></p>
				<p class="submit"><input type="submit" name="submit-backup" id="submit" class="button button-primary" value="<?php _e( "Backup" ) ?>"></p>
			</form>

			<h3><?php _e( "Import or Restore Theme Options", "wbf" ); ?></h3>

			<form action="admin.php?page=themeoptions-manager" method="POST" enctype="multipart/form-data"
			      id="export-themeoptions">
				<p><?php _e( "Select a file to restore, or upload one:" ); ?></p>
				<?php if ( ! empty( $backup_files ) ) : ?>
					<?php foreach ( $backup_files as $file ): ?>
						<p><label><input type="radio" name="local-backup-file" value="<?php echo $file['path'] ?>"><?php echo $file['name'] ?></label>&nbsp;<a href='<?php echo $file['url']; ?>' target="_blank" title="<?php _e( "Download: " . $file['name'] ); ?>">[<?php _e( "download" ) ?>]</a></p>
					<?php endforeach; ?>
				<?php else: ?>
					<p><?php _e( "No backup files available at the moment.", "wbf" ); ?></p>
				<?php endif; ?>
				<p>
					<label>
						<input type="file" name="remote-backup-file" id="backup-file"/>
					</label>
				</p>

				<p class="submit"><input type="submit" name="submit-restore" id="submit" class="button button-primary" value="<?php _e( "Import" ) ?>"></p>
			</form>
			<?php \WBF::print_copyright(); ?>
		</div>
	<?php
	}

	/**
	 * Backup current theme options to a file. Return the file url or throws Exception on fail.
	 * @throws \Exception
	 * @return bool|string
	 */
	private function _backup_options( $download = false ) {
		$current_settings = $this->get_current_active_theme_options();
		$backup_path      = WP_CONTENT_DIR . "/theme-options-backups";
		$backup_url       = WP_CONTENT_URL . "/theme-options-backups";
		if ( ! is_dir( $backup_path ) ) {
			mkdir( $backup_path );
		}
		$date            = date( 'Y-m-d-His' );
		$backup_filename = $this->get_option_id() . "-" . $date . ".options";

		if ( ! file_put_contents( $backup_path . "/" . $backup_filename, base64_encode( json_encode( $current_settings ) ) ) ) {
			throw new \Exception( __( "Unable to create the backup file: " . $backup_path . "/" . $backup_filename ) );
		}

		if ( $download ) { //Not used ATM
			header( 'Content-type: text/plain' );
			header( 'Content-Disposition: attachment; filename="' . $backup_filename . '"' );
			readfile( $backup_path . "/" . $backup_filename );
		}

		return $backup_url . "/" . $backup_filename;
	}

	/**
	 * Get the current theme options
	 * @return mixed|void
	 */
	public function get_current_active_theme_options() {
		$settings = get_option( $this->get_option_id() );

		return $settings;
	}

	/**
	 * Get the current id for theme options settings (aka the theme name)
	 * @return string
	 */
	public function get_option_id() {
		$optionsframework_settings = get_option( 'optionsframework' );
		// Gets the unique option id
		if ( isset( $optionsframework_settings['id'] ) ) {
			$option_name = $optionsframework_settings['id'];
		} else {
			$option_name = 'optionsframework';
		};

		return $option_name;
	}

	/**
	 * Read a file and restore the settings stored in it (if valid)
	 *
	 * @param array|string $file this can be a file uploaded through a form (the whole array) or a path to a file
	 *
	 * @return bool
	 * @throws \Exception
	 */
	private function _restore_options_from_file( $file ) {
		$optionsframework_settings = get_option( 'optionsframework' );
		$settings                  = array();

		if ( is_array( $file ) ) {
			//we have an uploaded file
			if ( isset( $file['tmp_name'] ) && is_uploaded_file( $file['tmp_name'] ) ) {
				$settings = $this->_get_backup_file_content( $file['tmp_name'] );
			} else {
				throw new \Exception( __( "Invalid backup file provided", "wbf" ) );
			}
		} else {
			//we have a file on disk
			if ( is_file( $file ) ) {
				$settings = $this->_get_backup_file_content( $file );
			} else {
				throw new \Exception( __( "Invalid backup file provided", "wbf" ) );
			}
		}
		//Restore the settings
		if ( $settings && ! empty( $settings ) ) {
			if ( ! update_option( $optionsframework_settings['id'], $settings ) ) {
				throw new \Exception( __( "The backup file and the current settings are identical", "wbf" ) );
			}
		} else {
			throw new \Exception( __( "Invalid backup file provided", "wbf" ) );
		}

		return true;
	}

	/**
	 * Read a backup file content. Returns FALSE if the file is not valid.
	 *
	 * @param string $filepath
	 *
	 * @return array|bool
	 */
	private function _get_backup_file_content( $filepath ) {
		if ( ! is_file( $filepath ) ) {
			return false;
		}

		$content  = file_get_contents( $filepath );
		$settings = json_decode( base64_decode( $content ), true );

		if ( ! is_array( $settings ) ) {
			return false;
		}

		return $settings;
	}

	/**
	 * Returns an array with all backup files
	 * @return array
	 */
	public function get_backupFiles() {
		$backup_path = WP_CONTENT_DIR . "/theme-options-backups";
		$files       = glob( $backup_path . "/*.options" );
		$output      = array();

		if ( is_array( $files ) ) {
			foreach ( $files as $f ) {
				$info     = pathinfo( $f );
				$output[] = array(
					'path' => $f,
					'url'  => WP_CONTENT_URL . "/theme-options-backups/" . $info['basename'],
					'name' => $info['basename']
				);
			}
		}

		return $output;
	}

	/**
	 * Loads the required javascript
	 *
	 * @since 1.7.0
	 */
	function enqueue_admin_scripts( $hook ) {
		if(!of_is_admin_framework_page($hook)){
			return;
		}

		// Enqueue custom option panel JS
		wp_enqueue_script( 'options-custom', OPTIONS_FRAMEWORK_DIRECTORY . 'js/options-custom.js', array(
			'jquery',
			'wp-color-picker'
		), Framework::VERSION );
		// Enqueue core CSS
		$core_stylesheet = \WBF::prefix_url('admin/css/optionsframework.css');
		if ($core_stylesheet != "")
			wp_enqueue_style('wbf-theme-options-style', $core_stylesheet, array('optionsframework'), false, 'all'); //Custom Theme Options CSS
		// Enqueue custom CSS
		$custom_stylesheet = wbf_locate_template_uri('assets/css/theme-options.css');
		if ($core_stylesheet != "")
			wp_enqueue_style('theme-options-style', $custom_stylesheet, array('optionsframework','wbf-theme-options-style'), false, 'all'); //Custom Theme Options CSS
		// Inline scripts from options-interface.php
		add_action( 'admin_head', array( $this, 'of_admin_head' ) );
	}

    /**
     * Builds out the options panel.
     *
     * If we were using the Settings API as it was intended we would use
     * do_settings_sections here.  But as we don't want the settings wrapped in a table,
     * we'll call our own custom optionsframework_fields.  See options-interface.php
     * for specifics on how each individual field is generated.
     *
     * Nonces are provided using the settings_fields()
     *
     * @since 1.7.0
     */
    function options_page() { ?>

        <div id="optionsframework-wrap" class="wrap">
            <?php $menu = $this->menu_settings(); ?>

            <div class="optionsframework-header">
                <h2><?php echo esc_html( $menu['page_title'] ); ?></h2>
            </div>

            <div id="optionsframework-content-wrapper">
                <div class="nav-tab-wrapper">
                    <ul>
                        <?php echo GUI::optionsframework_tabs(); ?>
                    </ul>
                </div>

                <?php settings_errors( 'options-framework' ); ?>

                <div id="optionsframework-metabox" class="metabox-holder">
                    <div id="optionsframework" class="postbox">
                        <form action="options.php" method="post">
                            <?php settings_fields( 'optionsframework' ); ?>
                            <?php GUI::optionsframework_fields(); /* Settings */ ?>
                            <div id="optionsframework-submit">
                                <input type="submit" class="button-primary" name="update" value="<?php esc_attr_e( 'Save Options', "wbf" ); ?>" />
                                <input type="submit" class="reset-button button-secondary" name="reset" value="<?php esc_attr_e( 'Restore Defaults', 'wbf' ); ?>" onclick="return confirm( '<?php print esc_js( __( 'Click OK to reset. Any theme settings will be lost!', 'wbf' ) ); ?>' );" />
                                <a href="admin.php?page=waboot_options&amp;clear_cache" class="clearcache-button button-secondary"><?php esc_attr_e( 'Clear Theme Cache', "wbf" ); ?></a>
                                <div class="clear"></div>
                            </div>
                        </form>
                    </div> <!-- / #container -->
                </div>
            </div> <!-- / #content-wrapper -->
            <?php do_action( 'optionsframework_after' ); ?>
        </div> <!-- / .wrap -->
    <?php
    }

	/**
	 * Validate Options.
	 *
	 * This runs after the submit/reset button has been clicked and
	 * validates the inputs.
	 *
	 * @uses $_POST['reset'] to restore default options
	 */
	function validate_options( $input ) {

		/*
		 * Restore Defaults.
		 *
		 * In the event that the user clicked the "Restore Defaults"
		 * button, the options defined in the theme's options.php
		 * file will be added to the option for the active theme.
		 */

		if ( isset( $_POST['reset'] ) ) {
			add_settings_error( 'options-framework', 'restore_defaults', __( 'Default options restored.', 'textdomain' ), 'updated fade' );
			return $this->get_default_values();
		}

		/*
		 * Update Settings
		 *
		 * This used to check for $_POST['update'], but has been updated
		 * to be compatible with the theme customizer introduced in WordPress 3.4
		 */

		$clean = array();
		$options = & Framework::get_registered_options();
		foreach ( $options as $option ) {

			if ( ! isset( $option['id'] ) ) {
				continue;
			}

			if ( ! isset( $option['type'] ) ) {
				continue;
			}

			$id = preg_replace( '/[^a-zA-Z0-9._\-]/', '', strtolower( $option['id'] ) );

			// Set checkbox to false if it wasn't sent in the $_POST
			if ( 'checkbox' == $option['type'] && ! isset( $input[$id] ) ) {
				$input[$id] = false;
			}

			// Set each item in the multicheck to false if it wasn't sent in the $_POST
			if ( 'multicheck' == $option['type'] && ! isset( $input[$id] ) ) {
				foreach ( $option['options'] as $key => $value ) {
					$input[$id][$key] = false;
				}
			}

			// For a value to be submitted to database it must pass through a sanitization filter
			if ( has_filter( 'of_sanitize_' . $option['type'] ) ) {
				if(isset($input[$id])){ //[WABOOT MOD]
					$clean[$id] = apply_filters( 'of_sanitize_' . $option['type'], $input[$id], $option );
				}
			}
		}

		// Hook to run after validation
		do_action( 'optionsframework_after_validate', $clean );

		return $clean;
	}

	/**
	 * Get the default values for all the theme options
	 *
	 * Get an array of all default values as set in
	 * options.php. The 'id','std' and 'type' keys need
	 * to be defined in the configuration array. In the
	 * event that these keys are not present the option
	 * will not be included in this function's output.
	 *
	 * @return array Re-keyed options configuration array.
	 *
	 */
	function get_default_values() {
		return Framework::get_default_values();
	}
}