<?php

namespace Waboot;

use function Waboot\functions\components\get_api_single_component_endpoint;
use function Waboot\functions\components\request_single_component;

class Component extends \WBF\modules\components\Component{
	/**
	 * @var string
	 */
	var $default_zone = "header";
	/**
	 * @var int
	 */
	var $default_priority = 10;
	/**
	 * @var string
	 */
	var $theme_relative_path;
	/**
	 * @var array
	 */
	var $registered_style_assets = [];
	/**
	 * @var string
	 */
	private $update_uri;

	public function __construct( array $component ) {
		parent::__construct( $component );
		$this->theme_relative_path = "components/".$this->directory_name;
		add_action('admin_init', [$this,'enqueue_updates']);
	}

	public function theme_options($options){
		$options = parent::theme_options($options);

		if(!function_exists("Waboot")) return $options;
		$zones = WabootLayout()->getZones();
		if(empty($zones) || !isset($zones['header'])) return $options;

		$zone_options = call_user_func(function() use($zones){
			$opts = [];
			foreach($zones as $k => $v){
				$opts[$k] = $v['slug'];
			}
			return $opts;
		});

		/*$options[] = [
			'name' => _x( 'Zone Settings', 'component settings', 'waboot' ),
			'desc' => _x( 'Choose zone settings for this component', 'component_settings', 'waboot' ),
			'type' => 'info',
			'id'   => strtolower($this->name).'_zone-settings_info',
			'component' => true,
			'component_name' => $this->name
		];*/
		$options[] = [
			'name' => _x( 'Position', 'component settings', 'waboot' ),
			'desc' => _x( 'Choose in which zone you want to display', 'component_settings', 'waboot' ),
            'class' => 'zone_position half_option',
			'id'   => strtolower($this->name).'_display_zone',
			'std'  => isset($this->default_zone) ? $this->default_zone : "header",
			'options' => $zone_options,
			'type' => 'select',
			'component' => true,
			'component_name' => $this->name
		];
		$options[] = [
			'name' => _x( 'Priority', 'component settings', 'waboot' ),
			'desc' => _x( 'Choose the display priority', 'component_settings', 'waboot' ),
            'class' => 'zone_priority half_option',
			'id'   => strtolower($this->name).'_display_priority',
			'std'  => isset($this->default_priority) ? (string) $this->default_priority : "10",
			'type' => 'text',
			'component' => true,
			'component_name' => $this->name
		];

		return $options;
	}

	public function get_display_zone(){
		$zone = $this->default_zone;
		if(function_exists("\\Waboot\\functions\\get_option")){
			$zone_opt = \Waboot\functions\get_option(strtolower($this->name)."_display_zone");
			if($zone_opt){
				$zone = $zone_opt;
			}
		}
		return $zone;
	}

	public function get_display_priority(){
		$p = $this->default_priority;
		if(function_exists("\\Waboot\\functions\\get_option")){
			$p_opt = \Waboot\functions\get_option(strtolower($this->name)."_display_priority");
			if($p_opt){
				$p = $p_opt;
			}
		}
		return $p;
	}

	/**
	 * Check whether an update is available
	 *
	 * @param bool $force
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function check_for_updates($force = false){
		$can_check_for_update = false;
		$needs_update = false;
		if(!$force){
			$update_check_status = \get_option('waboot_components_update_check_times',[]);
			if(!array_key_exists($this->name,$update_check_status)){
				$update_check_status[$this->name] = time();
				\update_option('waboot_components_update_check_times',$update_check_status);
				$can_check_for_update = true;
			}else{
				$last_check_time = $update_check_status[$this->name];
				$update_interval = (int) apply_filters('waboot/components/update_check_time_interval', 86400);
				if( is_int($update_interval) && $update_interval > 0 && (time() - $last_check_time) >= $update_interval ){
					$update_check_status[$this->name] = time();
					\update_option('waboot_components_update_check_times',$update_check_status);
					$can_check_for_update = true;
				}
			}
		}else{
			$can_check_for_update = true;
		}
		if($can_check_for_update) {
			$update_uri = $this->get_update_uri();
			if ( $update_uri !== '' ) {
				try {
					$data = request_single_component( $this->name, $update_uri );
					if ( isset( $data['version'] ) ) {
						$needs_update = version_compare( $this->get_version(), $data['version'], '<' );
					}
				} catch ( \Exception $e ) {
					throw new \Exception($e->getMessage());
				}
			}
		}
		return $needs_update;
	}

	/**
	 * Notify the user of available updates
	 *
	 * @hooked 'admin_init'
	 */
	public function enqueue_updates(){
		static $check_done;
		try{
			if($check_done !== true){
				$needs_update = $this->check_for_updates(true);
				if($needs_update){
					add_filter('wp_get_update_data', [$this,'alter_update_data'],11,2);
				}
			}
			$check_done = true;
		}catch(\Exception $e){
			WBF()->get_service_manager()->get_notice_manager()->add_notice(
				'unable_to_update_component_' . $this->name,
				sprintf( __( 'Unable to check for updates of component: %s because of the error: %s' ), $this->name, $e->getMessage() ),
				'error',
				'_flash_'
			);
		}
	}

	/**
	 * Alter WP Update data
	 *
	 * @wp_get_update_data by enqueue_updates()
	 */
	public function alter_update_data($update_data, $titles){
		if(!isset($update_data['theme-components'])){
			$update_data['theme-components'] = 1;
		}else{
			$update_data['theme-components']++;
		}
		$update_data['counts']['total'] = $update_data['counts']['total'] + 1;

		$update_data['title'] = $titles ? esc_attr( implode( ', ', $titles ) ) : '';

		$update_data['title'] = $update_data['title'].' , '.sprintf( _n( '%d Component Update', '%d Component Updates', $update_data['theme-components'] ), $update_data['theme-components'] );

		return $update_data;
	}

	/**
	 * Set the component update uri
	 *
	 * @param $uri
	 */
	public function set_update_uri($uri){
		$this->update_uri = $uri;
	}

	/**
	 * Get the component update uri
	 *
	 * @return string
	 */
	public function get_update_uri(){
		if(!isset($this->update_uri)){
			$component_data = get_file_data( $this->file, ['UpdateURI' => 'Update URI', 'Author' => 'Author', 'AuthorURI' => 'Author URI'] );
			if(!isset($component_data['UpdateURI']) || $component_data['UpdateURI'] === ''){
				if(isset($component_data['Author']) && strpos($component_data['Author'],'waga') !== false){
					$this->set_update_uri(get_api_single_component_endpoint($this->name));
				}
			}else{
				$this->set_update_uri('');
			}
		}
		return $this->update_uri;
	}
}