<?php

namespace Waboot\inc\core;

/**
 * Tries to require a list of files. Trigger a special error when can't.
 *
 * @param array $files
 */
function safeRequireFiles($files){
    if(!is_array($files) || count($files) === 0) return;
    foreach($files as $file){
        if (!$filepath = locate_template($file)) {
            throw new \RuntimeException(sprintf(__('Error locating %s for inclusion', LANG_TEXTDOMAIN), $file));
        }
        require_once $filepath;
    }
}

/**
 * Gets the variables needed to render an archive.php page
 *
 * @return array
 */
function getArchiveTemplate(){
    $vars = [];

    $o = get_queried_object();

    //@see https://developer.wordpress.org/files/2014/10/wp-hierarchy.png
    $tpl_base = 'templates/archive/';
    if(is_author()){
        $tpl[] = $tpl_base.'author-'.get_the_author_meta('user_nicename');
        $tpl[] = $tpl_base.'author-'.get_the_author_meta('ID');
        $tpl[] = $tpl_base.'author';
    }elseif($o instanceof \WP_Term){
        if($o->taxonomy === 'category'){
            $tpl[] = $tpl_base.'category'.'-'.$o->slug;
            $tpl[] = $tpl_base.'category-'.$o->term_id;
            $tpl[] = $tpl_base.'category';
        }else{
            $tpl[] = $tpl_base.$o->taxonomy.'-'.$o->slug;
            $tpl[] = $tpl_base.'taxonomy-'.$o->taxonomy.'-'.$o->slug;
            $tpl[] = $tpl_base.'taxonomy-'.$o->taxonomy;
            $tpl[] = $tpl_base.'taxonomy';
        }
    }elseif($o instanceof \WP_Post_Type){
        $tpl = $tpl_base.'archive-'.$o->name;
    }elseif(is_date()){
        $tpl = $tpl_base . 'date';
    }else{
        $tpl = '';
    }

    if($tpl !== '' || \is_array($tpl)){
        $tpl = locateTemplate($tpl);
    }

    $tlt2 = locate_template($tpl,false);

    return $tpl;
}

/**
 * A version of WP locate_template() that returns only the template name of located files
 *
 * @param array|string $templateNames
 * @param string $extension if the extension of the template files to look for
 *
 * @return string
 */
function locateTemplate($templateNames,$extension = '.php'){
    foreach ( (array) $templateNames as $template_name ) {
        if ( !$template_name )
            continue;

        if(!preg_match('/'.$extension.'$/',$template_name)){
            $template_filename = $template_name.$extension;
        }else{
            $template_filename = $template_name;
        }

        if ( file_exists(STYLESHEETPATH . '/' . $template_filename)) {
            return $template_name;
            break;
        } elseif ( file_exists(TEMPLATEPATH . '/' . $template_filename) ) {
            return $template_name;
            break;
        } elseif ( file_exists( ABSPATH . WPINC . '/theme-compat/' . $template_filename ) ) {
            return $template_name;
            break;
        }
    }

    return '';
}

/**
 * @return bool|Theme
 */
function Waboot(): Theme{
    return \Waboot\inc\core\helpers\Waboot();
}

/**
 * Returns Theme AssetsManager instance
 *
 * @return bool|AssetsManager
 */
function AssetsManager(): AssetsManager{
    return \Waboot\inc\core\helpers\AssetsManager();
}

/**
 * Returns Theme Layout instance
 *
 * @return bool|Layout
 */
function Layout(): Layout{
    return \Waboot\inc\core\helpers\Layout();
}