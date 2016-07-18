<?php
/**
Component Name: Blog - Timeline
Description: Enable timeline visualization for blog posts.
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
*/

class Blog_Timeline extends \WBF\modules\components\Component{
	/**
	 * This method will be executed at Wordpress startup (every page load)
	 */
	public function setup(){
		parent::setup();
		add_filter("waboot/layout/content/template",[$this,"set_blog_template"],10,2);
	}

	/**
	 * Set the blog template to render
	 */
	public function set_blog_template($tpl_args,$page_type){
		if($page_type == \WBF\components\utils\Utilities::PAGE_TYPE_BLOG_PAGE || $page_type == \WBF\components\utils\Utilities::PAGE_TYPE_DEFAULT_HOME){
			$tpl_args = ["components/blog_masonry/templates/content","blog-timeline"];
		}
		return $tpl_args;
	}

	/**
	 * Enqueue component scripts
	 */
	public function scripts(){
		wp_enqueue_script('component-blog_timeline-script', $this->directory_uri . '/assets/dist/js/blog-timeline.js', ['jquery'], false, false);
	}

	/**
	 * Enqueue component styles
	 */
	public function styles(){
		wp_enqueue_style('component-blog_timeline-style', $this->directory_uri . '/assets/dist/css/blog-timeline.css');
	}

	/**
	 * Register theme options
	 */
	public function register_options(){
		$orgzr = \WBF\modules\options\Organizer::getInstance();

		/*
		 * Standard group:
		 */

		$orgzr->set_group("components");

		$section_name = $this->name."_component";
		$additional_params = [
			'component' => true,
			'component_name' => $this->name
		];

		$orgzr->add_section($section_name,$this->name." Component",null,$additional_params);

		$orgzr->set_section($section_name);

		$orgzr->add([
			'type' => 'info',
			'name' => 'This component needs no administration options.',
			'desc' => 'Check <strong>theme options</strong> for additional settings'
		]);

		$orgzr->reset_group();
		$orgzr->reset_section();
	}
}