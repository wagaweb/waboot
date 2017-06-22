<?php
/**
Component Name: Blog - Masonry
Description: Enable masonry visualization for blog posts.
Category: Layout
Tags: jQuery, Masonry
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
*/

if(!class_exists("\\WBF\\modules\\components\\Component")) return;

class Blog_Masonry extends \WBF\modules\components\Component{
	/**
	 * This method will be executed at Wordpress startup (every page load)
	 */
	public function setup(){
		parent::setup();
		add_filter("waboot/layout/template_parts",[$this,"set_blog_template"],10,2);
		add_filter("waboot/layout/posts_wrapper/class",[$this,"set_blog_class"],10);
	}

	/**
	 * Set the blog template to render
	 */
	public function set_blog_template($tpl_args,$page_type){
		if( ( $page_type == \WBF\components\utils\Utilities::PAGE_TYPE_BLOG_PAGE || $page_type == \WBF\components\utils\Utilities::PAGE_TYPE_DEFAULT_HOME ) || $page_type == \WBF\components\utils\Utilities::PAGE_TYPE_COMMON && is_archive() ){
			$tpl_args = ["components/blog_masonry/templates/content","blog-masonry"];
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
		$classes[] = 'blog-masonry';
		return $classes;
	}

	/**
	 * Enqueue component scripts
	 */
	public function scripts(){
		wp_register_script('component-blog_masonry-script',$this->directory_uri . '/assets/vendor/masonry.pkgd.min.js',['jquery','imagesLoaded-js'],false,true);
		wp_register_script('component-blog_masonry-custom-script',$this->directory_uri . '/assets/dist/js/blog-masonry.js',['jquery','component-blog_masonry-script'],false,true);
		wp_enqueue_script('component-blog_masonry-custom-script');
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

		/*
		 * Blog Masonry Tab
		 */

		$orgzr->add_section("blog",__( 'Blog', 'waboot' ));

		$orgzr->add([
			'name' => _x( 'Masonry width column',"Blog Masonry Component Option", 'waboot' ),
			'desc' => _x( 'This is a sample checkbox.',"Blog Masonry Component Option", 'waboot' ),
			'id'   => $this->name.'_column_width',
			'std' => 'col-sm-4',
			'type' => 'select',
			'options' => array(
				'col-sm-4' => 'col-sm-4',
				'col-sm-3' => 'col-sm-3',
				'col-sm-6' => 'col-sm-6'
			)
		],"blog");

		$orgzr->reset_group();
		$orgzr->reset_section();
	}
}