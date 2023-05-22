<?php

namespace Waboot\inc\core;

use Waboot\inc\core\utils\Utilities;

/**
 * Renders main content into the "main" zone.
 */
function addMainContent(){
    /**
     * @var \WP_Query
     */
    global $wp_query;

    $page_type = Utilities::getCurrentPageType();

    switch($page_type){
        case Utilities::PAGE_TYPE_DEFAULT_HOME:
            $tpl_part = ['templates/blog',null];
            break;
        case Utilities::PAGE_TYPE_STATIC_HOME:
            $tpl_part = ['templates/page',null];
            break;
        case Utilities::PAGE_TYPE_BLOG_PAGE:
            $tpl_part = ['templates/blog',null];
            break;
        case Utilities::PAGE_TYPE_COMMON:
            if(is_attachment() && wp_attachment_is_image()){
                $tpl_part = ['templates/image',null]; //Note: this is a special case ported from Waboot 0.x
            }
            elseif($wp_query->is_single()){
                $tpl_part = ['templates/single',null];
            }elseif($wp_query->is_page()){
                $tpl_part = ['templates/page',null];
            }elseif($wp_query->is_author()){
                $tpl_part = ['templates/archive',null]; //From 3.1.0 we do not use the author.php anymore
            }elseif($wp_query->is_search()){
                $tpl_part = ['templates/search',null];
            }elseif($wp_query->is_archive()){
                $tpl_part = ['templates/archive',null];
            }elseif($wp_query->is_404()){
                $tpl_part = ['templates/404',null];
            }else{
                throw new \Exception( 'Unrecognized content type' );
            }
            break;
        default:
            throw new \Exception( 'Unrecognized page type' );
            break;
    }

    //Actually includes the template, making filterable.
    if(isset($tpl_part)){
        $tpl_part = apply_filters('waboot/layout/content/template',$tpl_part,$page_type);
        get_template_part($tpl_part[0],$tpl_part[1]);
    }
}
add_action('waboot/layout/content',__NAMESPACE__.'\\addMainContent');

/**
 * Injects Waboot custom templates
 *
 * @param array $page_templates
 * @param \WP_Theme $theme
 * @param \WP_Theme|null $post
 *
 * @return array
 */
function injectTemplates($page_templates, \WP_Theme $theme, $post){
	$template_directory = get_stylesheet_directory(). '/templates/parts-tpl';
	$template_directory = apply_filters('waboot/custom_template_parts_directory',$template_directory);
	$tpls = glob($template_directory. '/content-*.php');
	foreach ($tpls as $tpl){
		$basename = basename($tpl);
		$name = call_user_func(function() use ($basename) {
			preg_match('/^content-([a-z_-]+)/',$basename,$matches);
			if(isset($matches[1])){
				$name = $matches[1];
			}
			if(isset($name)) return $name; else return false;
		});
		if(!$name) continue;
		$page_templates[$name] = str_replace('_', ' ',ucfirst($name)). ' ' ._x('(parts)', 'Waboot Template Partials', LANG_TEXTDOMAIN);
	}
	return $page_templates;
}
add_filter('theme_page_templates',__NAMESPACE__."\\injectTemplates", 999, 3);

/**
 * Set post name as Body Class
 */
function addSlugBodyClass( $classes ) {
    global $post;
    if ( isset( $post ) ) {
        $classes[] = $post->post_type . '-' . $post->post_name;
    }
    return $classes;
}
add_filter( 'body_class',__NAMESPACE__."\\addSlugBodyClass" );

/**
 * Loads assets for comment reply
 */
function commentReplyJS() {
    //Comment reply script
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ){
        wp_enqueue_script( 'comment-reply' );
    }
}
add_action('wp_enqueue_scripts', __NAMESPACE__."\\commentReplyJS");

/**
 * Alter the default comments template location
 *
 * @param $themeTemplate
 *
 * @return string
 */
function alterCommentsTemplate($themeTemplate){
    $themeTemplate = locate_template('templates/comments.php');
    return $themeTemplate;
}
add_filter('comments_template', __NAMESPACE__."\\alterCommentsTemplate");
