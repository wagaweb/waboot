<?php
/**
Component Name: Bootstrap
Description: Enables Bootstrap on Waboot
Category: Layout
Tags: Bootstrap, Grid, Layout
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
*/

if(!class_exists("\\WBF\\modules\\components\\Component")) return;

class BootstrapComponent extends \WBF\modules\components\Component{

    public function setup(){
        parent::setup();
        add_filter('waboot/layout/grid_classes', [$this,'alter_grid_classes']);
    }

	/**
	 * @param array $classes
	 *
	 * @hooked 'waboot/layout/grid_classes'
	 *
	 * @return array
	 */
    public function alter_grid_classes($classes){
	    $classes = [
		    'row' => 'row',
		    'container' => 'container',
		    'container-fluid' => 'container-fluid',
		    'col_suffix' => 'col-'
	    ];
    	return $classes;
    }

	/**
	 * Register component scripts (called automatically)
	 */
    public function scripts(){
    	$bv = \Waboot\functions\get_option($this->name.'_bootstrap_version');

        //Enqueue scripts
        $assets = [
            'bootstrap-script' => [
                'uri' => $this->directory_uri . '/assets/vendor/bootstrap-'.$bv.'/js/bootstrap.min.js', //A valid uri
                'path' => $this->directory . '/assets/vendor/bootstrap-'.$bv.'/js/bootstrap.min.js', //A valid path
                'version' => '3.3.7',
                'deps' => ['jquery'],
                'in_footer' => false,
                'enqueue' => true
            ],
            'bootstrap-waboot-script' => [
                'uri' => $this->directory_uri . '/assets/dist/js/waboot-bootstrap.js', //A valid uri
                'path' => $this->directory . '/assets/dist/js/waboot-bootstrap.js', //A valid path
                'deps' => ['bootstrap-script'],
                'in_footer' => false,
                'enqueue' => true
            ],
        ];
        $a = new \WBF\components\assets\AssetsManager($assets);
        $a->enqueue();
    }

	/**
	 * Register component styles (called automatically)
	 */
    public function styles(){
	    $bv = \Waboot\functions\get_option($this->name.'_bootstrap_version');

        //Enqueue styles
	    $assets = [
		    'bootstrap-style' => [
			    'uri' => $this->directory_uri . '/assets/vendor/bootstrap-'.$bv.'/css/bootstrap.min.css', //A valid uri
			    'path' => $this->directory . '/assets/vendor/bootstrap-'.$bv.'/css/bootstrap-theme.min.css', //A valid path
			    'version' => '3.3.7',
			    'enqueue' => true
		    ],
		    'bootstrap-theme-style' => [
			    'uri' => $this->directory_uri . '/assets/vendor/bootstrap-'.$bv.'/css/bootstrap.min.css', //A valid uri
			    'path' => $this->directory . '/assets/vendor/bootstrap-'.$bv.'/css/bootstrap-theme.min.css', //A valid path
			    'version' => '3.3.7',
			    'enqueue' => false
		    ]
	    ];
	    $a = new \WBF\components\assets\AssetsManager($assets);
	    $a->enqueue();
    }

	public function register_options(){
		parent::register_options();
		$orgzr = \WBF\modules\options\Organizer::getInstance();

		$orgzr->set_group('components');

		$section_name = $this->name."_component";
		$additional_params = [
			'component' => true,
			'component_name' => $this->name
		];

		$orgzr->add_section($section_name,$this->name." Component",null,$additional_params);
		$orgzr->set_section($section_name);

		$orgzr->add([
			'name' => __( 'Bootstrap version', 'waboot' ),
			'desc' => __( 'Choose the Bootstrap version to include', 'waboot' ),
			'id'   => $this->name.'_bootstrap_version',
			'std'  => '3',
			'type' => "radio",
			'options' => [
				"3" => __("Bootstrap 3","waboot"),
				"4" => __("Bootstrap 4","waboot"),
			],
		],null,null,$additional_params);

		$orgzr->reset_group();
		$orgzr->reset_section();
	}
}