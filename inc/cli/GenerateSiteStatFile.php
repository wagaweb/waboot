<?php

namespace Waboot\inc\cli;

use Waboot\inc\core\cli\AbstractCommand;
use Waboot\inc\core\cli\CLIRuntimeException;

class GenerateSiteStatFile extends AbstractCommand
{
	protected string $outputPath;

	public static function getCommandDescription(): array
	{
		$description = parent::getCommandDescription();
		$description['shortdesc'] = 'Generate the state file needed for Waga Infra Daemon';
		$description['longdesc'] = '## EXAMPLES' . "\n\n" . 'wp generate-site-stat-file';
		$description['synopsis'][] = [
			'type' => 'assoc',
			'name' => 'output-path',
			'description' => 'Absolute path to the output directory',
			'optional' => true,
		];

		return $description;
	}

	public function run(array $args, array $assoc_args): int
	{
		try{
			if(isset($assoc_args['output-path']) && !empty($assoc_args['output-path'])) {
				$this->outputPath = $assoc_args['output-path'];
			}else{
				$this->outputPath = WP_CONTENT_DIR;
			}
			if(!is_dir($this->outputPath) || !is_writable($this->outputPath)) {
				throw new CLIRuntimeException('Invalid output path');
			}
			$siteName = get_bloginfo('name');
			$wpVersion = get_bloginfo('version');
			$pluginsInfo = $this->getPluginInfo();
			$data = [];
			$data['site_name'] = $siteName;
			$data['wp_version'] = $wpVersion;
			$data['plugins_info'] = $pluginsInfo;
			$dataJson = json_encode($data);
			$outputFileName = sanitize_title($siteName).'.json';
			$outputFilePath = $this->outputPath.'/'.$outputFileName;
			file_put_contents($outputFilePath, $dataJson);
			$this->success('File generated at: '.$outputFilePath);
			return 0;
		}catch (\Exception | \Throwable $e){
			$this->error($e->getMessage(), false);
			return 1;
		}
	}

	function getPluginInfo(): array
	{
		$adminAbsPath = str_replace( site_url(), rtrim(ABSPATH,'/'), admin_url() );
		if(!is_dir($adminAbsPath)){
			throw new CLIRuntimeException('Cannot find admin directory');
		}
		// Get all plugins
		include_once($adminAbsPath.'includes/plugin.php');
		$allPlugins = get_plugins();
		// Get active plugins
		$activePlugins = get_option('active_plugins');
		// Assemble array of name, version, and whether plugin is active (boolean)
		$pluginsData = [];
		foreach ( $allPlugins as $key => $value ) {
			$isActive = in_array($key, $activePlugins);
			$pluginsData[$key] = [
				'name' => $value['Name'],
				'version' => $value['Version'],
				'active' => $isActive,
				'update_available' => false
			];
		}
		//  Get the updates
		include_once($adminAbsPath.'includes/update.php');
		$pluginsUpdateData = get_plugin_updates();
		foreach ( $pluginsUpdateData as $pluginSlug => $dataObj ) {
			if(array_key_exists($pluginSlug, $pluginsData) && property_exists($dataObj, 'update')) {
				$pluginsData[$pluginSlug]['update_available'] = true;
				$pluginsData[$pluginSlug]['update'] = [
					"version" => $dataObj->update->new_version,
					"requires" => $dataObj->update->requires,
					"requires_php" => $dataObj->update->requires_php,
				];
			}
		}
		return $pluginsData;
	}
}