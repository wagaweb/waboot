<?php

namespace WBF\includes\pluginsframework;

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    Waboot_Plugin
 */
class Loader {

	/**
	 * The array of actions registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array $actions The actions registered with WordPress to fire when the plugin loads.
	 */
	protected $actions;

	/**
	 * The array of filters registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array $filters The filters registered with WordPress to fire when the plugin loads.
	 */
	protected $filters;

	public $public_plugin;

	public $admin_plugin;

	public $classes;

	/**
	 * Initialize the collections used to maintain the actions and filters.
	 *
	 * @since    1.0.0
	 *
	 * @param Plugin $caller
	 */
	public function __construct($caller = null) {

		$this->actions = array();
		$this->filters = array();

		if(isset($caller)){
			$class_name_parts = explode("\\",get_class($caller));
			if(is_file($caller->get_dir()."public/class-public.php")){
				$class_name = $class_name_parts[0].'\pub\Pub';
				$this->public_plugin = new $class_name($caller->get_plugin_name(), $caller->get_version(), $caller);
			}
			if(is_file($caller->get_dir()."admin/class-admin.php")){
				$class_name = $class_name_parts[0].'\admin\Admin';
				$this->admin_plugin = new $class_name($caller->get_plugin_name(), $caller->get_version(), $caller);
			}
		}
	}

	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @var      string $hook The name of the WordPress action that is being registered.
	 * @var      object $component A reference to the instance of the object on which the action is defined.
	 * @var      string $callback The name of the function definition on the $component.
	 * @var      int      Optional    $priority         The priority at which the function should be fired.
	 * @var      int      Optional    $accepted_args    The number of arguments that should be passed to the $callback.
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * A utility function that is used to register the actions and hooks into a single
	 * collection.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array $hooks The collection of hooks that is being registered (that is, actions or filters).
	 * @var      string $hook The name of the WordPress filter that is being registered.
	 * @var      object $component A reference to the instance of the object on which the filter is defined.
	 * @var      string $callback The name of the function definition on the $component.
	 * @var      int      Optional    $priority         The priority at which the function should be fired.
	 * @var      int      Optional    $accepted_args    The number of arguments that should be passed to the $callback.
	 * @return   type                                   The collection of actions and filters registered with WordPress.
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {

		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args
		);

		return $hooks;

	}

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @since    1.0.0
	 * @var      string $hook The name of the WordPress filter that is being registered.
	 * @var      object $component A reference to the instance of the object on which the filter is defined.
	 * @var      string $callback The name of the function definition on the $component.
	 * @var      int      Optional    $priority         The priority at which the function should be fired.
	 * @var      int      Optional    $accepted_args    The number of arguments that should be passed to the $callback.
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	public function add_class($class_obj){
		if(is_object($class_obj)){
			$this->classes[] = $class_obj;
		}
	}

	/**
	 * Register the filters and actions with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {

		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], array(
					$hook['component'],
					$hook['callback']
				), $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], array(
					$hook['component'],
					$hook['callback']
				), $hook['priority'], $hook['accepted_args'] );
		}

	}
}