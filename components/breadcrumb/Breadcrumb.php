<?php
/**
Component Name: Breadcrumb Component
Description: Breadcrumb Component
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
*/
class Breadcrumb extends \Waboot\Component {

	var $default_zone = "header";

	/**
	 * This method will be executed at Wordpress startup (every page load)
	 */
	public function setup(){
		parent::setup();
	}

	/**
	 * This method will be executed where the component is active
	 */
	public function run(){
		parent::run();
		$display_zone = $this->get_display_zone();
		$display_priority = $this->get_display_priority();

		if($display_zone == "header"){
			add_action("waboot/main/before",[$this,"display_tpl"]);
		}else{
			Waboot()->layout->add_zone_action($display_zone,[$this,"display_tpl"],intval($display_priority));
		}
	}

	/**
	 * Display component template
	 */
	public function display_tpl(){
		$v = new \WBF\components\mvc\HTMLView($this->relative_path."/templates/breadcrumb.php");
		$args = [
			'is_woocommerce' => function_exists('is_woocommerce') && is_woocommerce()
		];
		$v->clean()->display($args);
	}

	/**
	 * Register components options
	 */
	public function register_options() {
		parent::register_options();

		if(!class_exists('\WBF\modules\behaviors\BehaviorsManager')) return;

		$get_archive_pages_type = function($blacklist = array()){
			static $result;

			if(isset($result)) return $result;

			$archive_types = array(
				"archive" => __("Archive page","waboot"),
				"tag"     => __("Tag archive","waboot"),
				"tax"     => __("Taxonomy archive","waboot"),
			);
			$blacklist = array_unique(array_merge($blacklist,array()));
			$result = array();
			foreach($archive_types as $name => $label){
				if(!in_array($name,$blacklist)){
					$result[$name] = $label;
				}
			}

			return $result;
		};

		$bd_locs = array_merge(array("homepage"=>"Homepage"),wbf_get_filtered_post_types(),$get_archive_pages_type());

		if (!empty($bd_locs)) {
			$orgzr = \WBF\modules\options\Organizer::getInstance();

			$orgzr->set_group($this->name."_component");

			$orgzr->add_section("layout",_x("Layout","Theme options section","waboot"));

			$orgzr->add(array(
				'id' => 'breadcrumb_locations',
				'name' => __('Breadcrumb Locations', 'waboot'),
				'desc' => __('Where to show breadcrumb', 'waboot'),
				'type' => 'multicheck',
				'options' => $bd_locs,
				'std' => array(
					'homepage' => 1,
					'post' => 1,
					'page' => 1,
					'archive' => 1,
					'tag' => 1,
					'tax' => 1
				)
			),"layout");

			$orgzr->reset_group();
			$orgzr->reset_section();
		}
	}

	/**
	 * Display the breadcrumb for $post_id or global $post->ID
	 *
	 * @param null $post_id
	 * @param string $current_location the current location of breadcrumb. Not used at the moment, but it can be any arbitrary string.
	 * @param array $args settings for breadcrumb (see: trail() documentation)
	 */
	public static function do_breadcrumb($post_id = null, $current_location = "", $args = array()) {
		global $post;

		//Get post ID
		if(!isset($post_id)){
			if(isset($post) && isset($post->ID) && $post->ID != 0){
				$post_id = $post->ID;
			}
		}

		if(!function_exists( "\\WBF\\components\\breadcrumb\\trail" )) return;

		if(is_404()) return;

		$current_page_type = \WBF\components\utils\Utilities::get_current_page_type();

		$args = wp_parse_args($args, array(
			'container' => "div",
			'separator' => "/",
			'show_browse' => false,
			'additional_classes' => ""
		));

		$allowed_locations = call_user_func(function(){
			$bc_locations = \Waboot\functions\get_option('breadcrumb_locations',[]);
			$allowed = array();
			foreach($bc_locations as $k => $v){
				if($v == "1"){
					$allowed[] = $k;
				}
			}
			return $allowed;
		});

		if($current_page_type != "common"){
			//We are in some sort of homepage
			if(in_array("homepage", $allowed_locations)) {
				\WBF\components\breadcrumb\trail($args);
			}
		}else{
			//We are NOT in some sort of homepage
			if(!is_archive() && !is_search() && isset($post_id)){
				//We are in a common page
				$current_post_type = get_post_type($post_id);
				if (!isset($post_id) || $post_id == 0 || !$current_post_type) return;
				if(in_array($current_post_type, $allowed_locations)) {
					\WBF\components\breadcrumb\trail($args);
				}
			}else{
				//We are in some sort of archive
				$show_bc = false;
				if(is_tag() && in_array('tag',$allowed_locations)){
					$show_bc = true;
				}elseif(is_tax() && in_array('tax',$allowed_locations)){
					$show_bc = true;
				}elseif(is_archive() && in_array('archive',$allowed_locations)){
					$show_bc = true;
				}
				if($show_bc){
					\WBF\components\breadcrumb\trail($args);
				}
			}
		}
	}
}