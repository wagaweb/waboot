<?php

interface Waboot_Template_Plugin_Interface {
	public function register_templates( $atts );

	public function view_template( $template );
}

class Waboot_Template_Plugin extends Waboot_Plugin implements Waboot_Template_Plugin_Interface {
	protected $templates;
	protected $ctp_templates;
	protected $templates_paths;

	public function __construct( $plugin_name, $dir, $version ) {
		parent::__construct( $plugin_name, $dir, $version );
		$this->templates       = array();
		$this->templates_paths = array();
		$this->loader->add_filter( 'page_attributes_dropdown_pages_args', $this, "register_templates" );
		$this->loader->add_filter( 'wp_insert_post_data', $this, "register_templates" );
		$this->loader->add_filter( 'template_include', $this, "view_template" );
	}

	public function add_template( $template_name, $label, $path ) {
		$current_wp_templates = wp_get_theme()->get_page_templates(); //current wp registered templates

		$this->templates[ $template_name ]       = __( $label, $this->plugin_name );
		$this->templates_paths[ $template_name ] = $path;
		$current_wp_templates                    = array_merge( $current_wp_templates, $this->templates );

		return $this->templates;
	}

	public function add_cpt_template( $template_name, $path ) {
		$this->ctp_templates[]                   = $template_name;
		$this->templates_paths[ $template_name ] = $path;

		return $this->ctp_templates;
	}

	/**
	 * Adds our template to the pages cache in order to trick WordPress
	 * into thinking the template file exists where it doens't really exist.
	 *
	 * @version    1.0.0
	 * @since    1.0.0
	 *
	 * @param   array $atts The attributes for the page attributes dropdown
	 *
	 * @return array
	 */
	public function register_templates( $atts ) {
		// Create the key used for the themes cache
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		// Retrieve the cache list. If it doesn't exist, or it's empty prepare an array
		$templates = wp_cache_get( $cache_key, 'themes' );
		if ( empty( $templates ) ) {
			$templates = array();
		}

		// Since we've updated the cache, we need to delete the old cache
		wp_cache_delete( $cache_key, 'themes' );

		// Now add our template to the list of templates by merging our templates
		// with the existing templates array from the cache.
		$templates = array_merge( $templates, $this->templates );

		// Add the modified cache to allow WordPress to pick it up for listing
		// available templates
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );

		return $atts;
	}

	/**
	 * Checks if the template is assigned to the page
	 *
	 * @version    1.0.0
	 * @since    1.0.0
	 */
	public function view_template( $template ) {
		global $post;

		// If no posts found, return to
		// avoid "Trying to get property of non-object" error
		if ( ! isset( $post ) ) {
			return $template;
		}

		$required_tpl = get_post_meta( $post->ID, '_wp_page_template', true ); //Get the template set via wp editor

		//If it is empty it means we have to check the wp template hierarchy...
		if ( $required_tpl == "" ) {
			//Check if plugin has a template for current post\page
			$tpl_filename = basename( $template );
			if ( in_array( $tpl_filename, $this->ctp_templates ) ) {
				$file = $this->templates_paths[ $tpl_filename ];
				return $file;
			}

			if(is_archive()){
				$q_obj = get_queried_object();
				if(is_category()){
					$possible_templates = array(
						"category-{$q_obj->slug}",
						"category-{$q_obj->term_id}.php"
					);
				}elseif(is_tag()){
					$possible_templates = array(
						"tag-{$q_obj->slug}",
						"tag-{$q_obj->term_id}.php"
					);
				}elseif(is_tax()){
					$possible_templates = array(
						"taxonomy-{$q_obj->taxonomy}-{$q_obj->slug}.php",
						"taxonomy-{$q_obj->taxonomy}.php"
					);
				}else{
					$post_type = get_post_type( $post->ID );
					$possible_templates = array(
						"archive-{$post_type}.php"
					);
				}
			}else{
				$post_type = get_post_type( $post->ID );
				$possible_templates = array(
					"attachment.php",
					"single-" . $post_type . ".php",
					"single-post.php",
					$post_type . ".php",
					"single-" . $post->ID . ".php"
				);
			}

			foreach ( $possible_templates as $tpl_filename ) {
				if ( in_array( $tpl_filename, $this->ctp_templates ) ) {
					$file = $this->templates_paths[ $tpl_filename ];

					return $file;
				}
			}
		}

		if ( ! isset( $this->templates[ $required_tpl ] ) ) {
			return $template;
		}

		$file = $this->templates_paths[ $required_tpl ];

		if ( file_exists( $file ) ) {
			return $file;
		}

		return $template;
	}
}