<?php

namespace WBF\includes;

class Plugin_Update_Checker extends \PluginUpdateChecker{
	/**
	 * Class constructor.
	 *
	 * @param string $metadataUrl The URL of the plugin's metadata file.
	 * @param string $pluginFile Fully qualified path to the main plugin file.
	 * @param string $slug The plugin's 'slug'. If not specified, the filename part of $pluginFile sans '.php' will be used as the slug.
	 * @param integer $checkPeriod How often to check for updates (in hours). Defaults to checking every 12 hours. Set to 0 to disable automatic update checks.
	 * @param string $optionName Where to store book-keeping info about update checks. Defaults to 'external_updates-$slug'.
	 * @param string $muPluginFile Optional. The plugin filename relative to the mu-plugins directory.
	 */
	public function __construct($metadataUrl, $pluginFile, $slug = '', $checkLicense = false, $checkPeriod = 12, $optionName = '', $muPluginFile = ''){
		$this->metadataUrl = $metadataUrl;
		$this->pluginAbsolutePath = $pluginFile;
		$this->pluginFile = plugin_basename($this->pluginAbsolutePath);
		$this->muPluginFile = $muPluginFile;
		$this->checkPeriod = $checkPeriod;
		$this->slug = $slug;
		$this->optionName = $optionName;
		$this->debugMode = defined('WP_DEBUG') && WP_DEBUG;

		//If no slug is specified, use the name of the main plugin file as the slug.
		//For example, 'my-cool-plugin/cool-plugin.php' becomes 'cool-plugin'.
		if ( empty($this->slug) ){
			$this->slug = basename($this->pluginFile, '.php');
		}

		if ( empty($this->optionName) ){
			$this->optionName = 'external_updates-' . $this->slug;
		}

		//Backwards compatibility: If the plugin is a mu-plugin but no $muPluginFile is specified, assume
		//it's the same as $pluginFile given that it's not in a subdirectory (WP only looks in the base dir).
		if ( empty($this->muPluginFile) && (strpbrk($this->pluginFile, '/\\') === false) && $this->isMuPlugin() ) {
			$this->muPluginFile = $this->pluginFile;
		}

		$checkLicense = true; //todo: Se il plugin framework deve essere indipendente da wbf... nn si dovrebbe controllare la licenza sul license manager del WBF

		if($checkLicense){
			if(\WBF\admin\License_Manager::get_license_status() == "Active") {
				$this->installHooks();
				$this->remove_not_upgradable_plugin($this->slug);
			}else{
				$update = $this->maybeCheckForUpdates();
				if(!is_null($update) && $update != false){
					$this->add_not_upgradable_plugin($this->slug);
					//Inject the fake update...
					add_filter('site_transient_update_plugins', array($this,'injectFakeUpdate')); //WP 3.0+
					add_filter('transient_update_plugins', array($this,'injectFakeUpdate')); //WP 2.8+
				}
			}
		}else{
			$this->installHooks();
		}
	}


	/**
	 * Check for updates if the configured check interval has already elapsed.
	 * Will use a shorter check interval on certain admin pages like "Dashboard -> Updates" or when doing cron.
	 *
	 * You can override the default behaviour by using the "puc_check_now-$slug" filter.
	 * The filter callback will be passed three parameters:
	 *     - Current decision. TRUE = check updates now, FALSE = don't check now.
	 *     - Last check time as a Unix timestamp.
	 *     - Configured check period in hours.
	 * Return TRUE to check for updates immediately, or FALSE to cancel.
	 *
	 * This method is declared public because it's a hook callback. Calling it directly is not recommended.
	 */
	public function maybeCheckForUpdates(){
		if ( empty($this->checkPeriod) ){
			return;
		}

		$currentFilter = current_filter();
		if ( in_array($currentFilter, array('load-update-core.php', 'upgrader_process_complete')) ) {
			//Check more often when the user visits "Dashboard -> Updates" or does a bulk update.
			$timeout = 60;
		} else if ( in_array($currentFilter, array('load-plugins.php', 'load-update.php')) ) {
			//Also check more often on the "Plugins" page and /wp-admin/update.php.
			$timeout = 3600;
		} else if ( $this->throttleRedundantChecks && ($this->getUpdate() !== null) ) {
			//Check less frequently if it's already known that an update is available.
			$timeout = $this->throttledCheckPeriod * 3600;
		} else if ( defined('DOING_CRON') && constant('DOING_CRON') ) {
			//WordPress cron schedules are not exact, so lets do an update check even
			//if slightly less than $checkPeriod hours have elapsed since the last check.
			$cronFuzziness = 20 * 60;
			$timeout = $this->checkPeriod * 3600 - $cronFuzziness;
		} else {
			$timeout = $this->checkPeriod * 3600;
		}

		$state = $this->getUpdateState();
		$shouldCheck =
			empty($state) ||
			!isset($state->lastCheck) ||
			( (time() - $state->lastCheck) >= $timeout );

		//Let plugin authors substitute their own algorithm.
		$shouldCheck = apply_filters(
			'puc_check_now-' . $this->slug,
			$shouldCheck,
			(!empty($state) && isset($state->lastCheck)) ? $state->lastCheck : 0,
			$this->checkPeriod
		);

		$shouldCheck = true; //Hack :)

		if ( $shouldCheck ){
			$result = $this->checkForUpdates();
			return $result;
		}

		return false;
	}

	/**
	 * Insert a fake update
	 *
	 * @param \StdClass $updates Update list.
	 * @return \StdClass Modified update list.
	 */
	public function injectFakeUpdate($updates){
		//Is there an update to insert?
		$update = $this->getUpdate();

		//No update notifications for mu-plugins unless explicitly enabled. The MU plugin file
		//is usually different from the main plugin file so the update wouldn't show up properly anyway.
		if ( !empty($update) && empty($this->muPluginFile) && $this->isMuPlugin() ) {
			$update = null;
		}

		if ( !empty($update) ) {
			//Let plugins filter the update info before it's passed on to WordPress.
			$update = apply_filters('puc_pre_inject_update-' . $this->slug, $update);
			if ( !is_object($updates) ) {
				$updates = new \StdClass();
				$updates->response = array();
			}

			$wpUpdate = $update->toWpFormat();
			$pluginFile = $this->pluginFile;

			if ( $this->isMuPlugin() ) {
				//WP does not support automatic update installation for mu-plugins, but we can still display a notice.
				$wpUpdate->package = null;
				$pluginFile = $this->muPluginFile;
			}

			//Set the pkg to null
			$wpUpdate->package = null;

			$updates->response[$pluginFile] = $wpUpdate;

		} else if ( isset($updates, $updates->response) ) {
			unset($updates->response[$this->pluginFile]);
			if ( !empty($this->muPluginFile) ) {
				unset($updates->response[$this->muPluginFile]);
			}
		}

		return $updates;
	}

	protected function remove_not_upgradable_plugin($plugin_name){
		$opt = get_option("wbf_unable_to_update_plugins",array());
		foreach($opt as $k => $plg){
			if($plg == $plugin_name){
				unset($opt[$k]);
			}
		}
		update_option("wbf_unable_to_update_plugins",$opt);
	}

	protected function add_not_upgradable_plugin($plugin_name){
		$opt = get_option("wbf_unable_to_update_plugins",array());
		if(!in_array($plugin_name,$opt))
			$opt[] = $plugin_name;
		update_option("wbf_unable_to_update_plugins",$opt);
	}

	protected function clear_not_upgradable_plugins(){
		update_option("wbf_unable_to_update_plugins",array());
	}
}