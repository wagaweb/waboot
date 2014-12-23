<?php

class Waboot_Options_Framework_Admin extends Options_Framework_Admin{

	static function menu_settings() {

		$menu = array(
			'page_title' => __('Theme Options', 'waboot'),
			'menu_title' => __('Theme Options', 'waboot'),
			'capability' => 'edit_theme_options',
			'old_menu_slug' => 'options-framework',
			'menu_slug' => 'waboot_options'
		);

		return apply_filters('optionsframework_menu', $menu);
	}

	public function init() {
		parent::init();
		//remove_action( 'admin_menu', array( $this, 'add_options_page' ) );
		add_action( 'admin_menu', array( $this, 'add_man_page' ), 12 );
		//add_action( 'admin_menu', array( $this, 'add_additional_appearance_link' ) );
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
		$this->options_screen = add_submenu_page( "waboot_options", $menu['page_title'], $menu['menu_title'], $menu['capability'], $menu['menu_slug'], array(
				$this,
				'options_page'
			) );
	}

	/**
	 * Add "Manage Theme Options" subpage to Waboot Menu
	 */
	public function add_man_page() {
		add_submenu_page( "waboot_options", __( "Theme Options Manager", "waboot" ), __( "Import/Export", "waboot" ), "edit_theme_options", "themeoptions-manager", array(
				$this,
				'do_man_page'
		) );
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
							waboot_admin_show_message( __( "Backup successfully created!", "waboot" ), "updated" );
						} catch ( Exception $e ) {
							waboot_admin_show_message( $e->getMessage(), "error" );
						}
						break;
					default:
						waboot_admin_show_message( __( "Invalid option selected", "waboot" ), "error" );
						break;
				}
			}
			if ( isset( $_POST['submit-restore'] ) ) {
				if ( isset( $_FILES['remote-backup-file'] ) && $_FILES['remote-backup-file']['tmp_name'] != "" ) {
					$file = $_FILES['remote-backup-file'];
					if ( $file['error'] == UPLOAD_ERR_OK && is_uploaded_file( $file['tmp_name'] ) ) {
						try {
							$this->_restore_options_from_file( $file );
							waboot_admin_show_message( __( "Backup successfully restored!", "waboot" ), "updated" );
						} catch ( Exception $e ) {
							waboot_admin_show_message( $e->getMessage(), "error" );
						}
					} else {
						waboot_admin_show_message( __( "Unable to upload the file.", "waboot" ), "error" );
					}
				} elseif ( isset( $_POST['local-backup-file'] ) ) {
					$file = $_POST['local-backup-file'];
					try {
						$this->_restore_options_from_file( $file );
						waboot_admin_show_message( __( "Backup successfully restored!", "waboot" ), "updated" );
					} catch ( Exception $e ) {
						waboot_admin_show_message( $e->getMessage(), "error" );
					}
				} else {
					waboot_admin_show_message( __( "No backup file provided.", "waboot" ), "error" );
				}
			}
			$backup_files = $this->get_backupFiles();
			?>
			<h2><?php _e( "Theme Options Manager", "waboot" ); ?></h2>

			<h3><?php _e( "Export or Backup Theme Options", "waboot" ); ?></h3>

			<form action="admin.php?page=themeoptions-manager" method="POST" id="export-themeoptions">
				<p><label><input type="radio" name="option"
				                 value="backup"> <?php _e( "Backup current Theme Options on the disk" ); ?></label></p>

				<p class="submit"><input type="submit" name="submit-backup" id="submit" class="button button-primary"
				                         value="<?php _e( "Backup" ) ?>"></p>
			</form>

			<h3><?php _e( "Import or Restore Theme Options", "waboot" ); ?></h3>

			<form action="admin.php?page=themeoptions-manager" method="POST" enctype="multipart/form-data"
			      id="export-themeoptions">
				<p><?php _e( "Select a file to restore, or upload one:" ); ?></p>
				<?php if ( ! empty( $backup_files ) ) : ?>
					<?php foreach ( $backup_files as $file ): ?>
						<p><label><input type="radio" name="local-backup-file"
						                 value="<?php echo $file['path'] ?>"><?php echo $file['name'] ?></label>&nbsp;<a
								href='<?php echo $file['url']; ?>' target="_blank"
								title="<?php _e( "Download: " . $file['name'] ); ?>">[<?php _e( "download" ) ?>]</a></p>
					<?php endforeach; ?>
				<?php else: ?>
					<p><?php _e( "No backup files available at the moment.", "waboot" ); ?></p>
				<?php endif; ?>
				<p>
					<label>
						<input type="file" name="remote-backup-file" id="backup-file"/>
					</label>
				</p>

				<p class="submit"><input type="submit" name="submit-restore" id="submit" class="button button-primary"
				                         value="<?php _e( "Import" ) ?>"></p>
			</form>
		</div>
	<?php
	}

	/**
	 * Backup current theme options to a file. Return the file url or throws Exception on fail.
	 * @throws Exception
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
			throw new Exception( __( "Unable to create the backup file: " . $backup_path . "/" . $backup_filename ) );
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
	 * @throws Exception
	 */
	private function _restore_options_from_file( $file ) {
		$optionsframework_settings = get_option( 'optionsframework' );
		$settings                  = array();

		if ( is_array( $file ) ) {
			//we have an uploaded file
			if ( isset( $file['tmp_name'] ) && is_uploaded_file( $file['tmp_name'] ) ) {
				$settings = $this->_get_backup_file_content( $file['tmp_name'] );
			} else {
				throw new Exception( __( "Invalid backup file provided", "waboot" ) );
			}
		} else {
			//we have a file on disk
			if ( is_file( $file ) ) {
				$settings = $this->_get_backup_file_content( $file );
			} else {
				throw new Exception( __( "Invalid backup file provided", "waboot" ) );
			}
		}
		//Restore the settings
		if ( $settings && ! empty( $settings ) ) {
			if ( ! update_option( $optionsframework_settings['id'], $settings ) ) {
				throw new Exception( __( "The backup file and the current settings are identical", "waboot" ) );
			}
		} else {
			throw new Exception( __( "Invalid backup file provided", "waboot" ) );
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
		if(!wbf_is_admin_of_page($hook)){
			return;
		}

		// Enqueue custom option panel JS
		wp_enqueue_script( 'options-custom', OPTIONS_FRAMEWORK_DIRECTORY . 'js/options-custom.js', array(
			'jquery',
			'wp-color-picker'
		), Options_Framework::VERSION );
		// Enqueue custom CSS
		$stylesheet = waboot_locate_template_uri('wbf/admin/css/waboot-optionsframework.css');
		if ($stylesheet != "")
			wp_enqueue_style('waboot-theme-options-style', $stylesheet, array('optionsframework'), '1.0.0', 'all'); //Custom Theme Options CSS
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
                        <?php echo Waboot_Options_Interface::optionsframework_tabs(); ?>
                    </ul>
                </div>

                <?php settings_errors( 'options-framework' ); ?>

                <div id="optionsframework-metabox" class="metabox-holder">
                    <div id="optionsframework" class="postbox">
                        <form action="options.php" method="post">
                            <?php settings_fields( 'optionsframework' ); ?>
                            <?php Waboot_Options_Interface::optionsframework_fields(); /* Settings */ ?>
                            <div id="optionsframework-submit">
                                <input type="submit" class="button-primary" name="update" value="<?php esc_attr_e( 'Save Options', 'waboot' ); ?>" />
                                <input type="submit" class="reset-button button-secondary" name="reset" value="<?php esc_attr_e( 'Restore Defaults', 'waboot' ); ?>" onclick="return confirm( '<?php print esc_js( __( 'Click OK to reset. Any theme settings will be lost!', 'waboot' ) ); ?>' );" />
                                <a href="admin.php?page=waboot_options&amp;clear_cache" class="reset-button button-secondary"><?php esc_attr_e( 'Clear Theme Cache', 'waboot' ); ?></a>
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
	 * Add options menu item to admin bar
	 */

	function optionsframework_admin_bar() {

		$menu = $this->menu_settings();
		global $wp_admin_bar;

		$wp_admin_bar->add_menu( array(
			'parent' => 'appearance',
			'id' => 'of_theme_options',
			'title' => __( 'Theme Options', 'waboot' ),
			'href' => admin_url( 'admin.php?page=' . $menu['menu_slug'] )
		) );
	}
}