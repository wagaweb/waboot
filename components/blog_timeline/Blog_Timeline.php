<?php
/**
Component Name: Blog - Timeline
Description: Enable timeline visualization for blog posts.
Category: Layout
Tags: jQuery, Timeline
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
*/

if(!class_exists("\\WBF\\modules\\components\\Component")) return;

class Blog_Timeline extends \WBF\modules\components\Component{
	/**
	 * This method will be executed at Wordpress startup (every page load)
	 */
	public function setup(){
		parent::setup();
		add_filter("waboot/layout/template_parts",[$this,"set_blog_template"],10,2);
		add_filter("waboot/layout/posts_wrapper/class",[$this,"set_blog_class"],10);
		Waboot()->add_component_style('component-blog_timeline-style', $this->directory_uri . '/assets/dist/css/blog-timeline.css');
	}

	/**
	 * Set the blog template to render
	 */
	public function set_blog_template($tpl_args,$page_type){
		if( ( $page_type == \WBF\components\utils\Utilities::PAGE_TYPE_BLOG_PAGE || $page_type == \WBF\components\utils\Utilities::PAGE_TYPE_DEFAULT_HOME ) || $page_type == \WBF\components\utils\Utilities::PAGE_TYPE_COMMON && is_archive() ){
			$tpl_args = ["components/blog_timeline/templates/content","blog-timeline"];
		}
		return $tpl_args;
	}

	/**
	 * Set blog classes
	 *
	 * @param $classes
	 *
	 * @return mixed
	 */
	public function set_blog_class($classes){
		//remove any blog-
		$classes = array_filter($classes,function($class){
			return !preg_match("/blog-/",$class);
		});
		//add our:
		$classes[] = 'blog-timeline';
		return $classes;
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
		//wp_enqueue_style('component-blog_timeline-style', $this->directory_uri . '/assets/dist/css/blog-timeline.css');
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