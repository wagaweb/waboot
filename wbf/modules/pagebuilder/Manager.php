<?php

namespace WBF\modules\pagebuilder;

class Manager {

    static $special_menu_labels = array("{menu}");
    static $postmetas = array(
      'content' => '_wbpbcontent',
      'blocks' => '_wbpbblocks',
	  'sections' => '_wbpbsections'
    );

    static function init() {
        add_action( 'admin_enqueue_scripts', '\WBF\modules\pagebuilder\Manager::scripts' );
        add_action( 'admin_enqueue_scripts', '\WBF\modules\pagebuilder\Manager::styles' );

        add_action( 'save_post', '\WBF\modules\pagebuilder\Manager::save_post', 10, 2 );
	    add_filter( 'the_content', '\WBF\modules\pagebuilder\Manager::clean_up_the_content_filter' );
    }

    static function styles() {
	    global $pagenow;
	    if ( in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) ) {
		    wp_register_style( 'wb-pagebuilder', get_template_directory_uri() . "/wbf/admin/css/pagebuilder.css" );

		    wp_enqueue_style( 'font-awesome-pb', wbf_locate_template_uri( 'assets/css/font-awesome.min.css' ) );
		    wp_enqueue_style( 'wb-pagebuilder' );

		    $current_builder = get_current_builder();
		    if ( $current_builder ) {
			    wp_enqueue_style( $current_builder . "-pagebuilder-css", get_stylesheet_directory_uri() . "/assets/css/" . $current_builder . "-pagebuilder.css", array( 'wb-pagebuilder' ) );
		    }
	    }
    }

    static function scripts() {
        global $pagenow;
        if ( in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) ) {
            $global_deps = array(
                "jquery",
                "jquery-ui-sortable",
                "jquery-ui-draggable",
                "jquery-ui-selectable",
                "jquery-ui-dialog",
                "jquery-modal",
                "underscore"
            );
            $loc_array = array(
                'url' => get_bloginfo( "url" ) . "/wp-admin/admin-ajax.php",
                'tabTitle' => __( "Waboot Page Builder", "waboot" ),
                'toolbar'  => GUI::generate_toolbar(),
                'tools' => self::_get_tools(),
                'blocks'   => self::_get_blocks(),
                'templates' => array(
                    'editor' => GUI::editor_frontend_tpl("<%= id %>-modal","<%= id %>","<%= content %>")
                )
            );

            wp_register_script( 'jquery-modal', get_template_directory_uri() . "/wbf/vendor/jquery-modal/jquery.modal.min.js", array( "jquery" ) );

            wp_register_script( 'wb-pagebuilder-toolbar', get_template_directory_uri() . "/wbf/admin/js/page-builder-toolbar.js", $global_deps );
            wp_register_script( 'wb-pagebuilder-editor', get_template_directory_uri() . "/wbf/admin/js/page-builder-editor.js", array_merge($global_deps,array("wb-pagebuilder-toolbar")) );
            wp_register_script( 'wb-pagebuilder', get_template_directory_uri() . "/wbf/admin/js/page-builder.js", array_merge($global_deps,array("wb-pagebuilder-editor")) );
            wp_localize_script( 'wb-pagebuilder', 'wbpbData', $loc_array);

            wp_enqueue_script( 'jquery-modal' );
            wp_enqueue_script( 'wb-pagebuilder-editor' );
            wp_enqueue_script( 'wb-pagebuilder' );
        }
    }

    static function _get_tools() {
        global $wbpagebuilder;
        $tools = array();
        foreach ( $wbpagebuilder->toolbar as $label => $block_info ) {
            if(in_array($label,self::$special_menu_labels)){
                switch($label){
                    case '{menu}':
                        foreach($block_info['options'] as $block_label => $block_data){
                            $tools[ $block_data['block'] ] = $block_data;
                        }
                        break;
                    default:
                        continue;
                        break;
                }
            }else{
                $tools[ $block_info['block'] ] = $block_info;
            }
        }

        return $tools;
    }

    static function _get_blocks() {
        global $wbpagebuilder;
        $blocks = array();
        foreach ( $wbpagebuilder->blocks as $block_name => $block_data ) {
            if ( $block_name == "main-container" ) {
                $blocks[$block_name]['layout'] = "*";
                $blocks[$block_name]['info'] = $wbpagebuilder->get_block_attrs($block_name);
            } else {
                if (method_exists($wbpagebuilder, $block_name)) {
                    $blocks[$block_name]['layout'] = $wbpagebuilder->get_block($block_name);
                    $blocks[$block_name]['info'] = $wbpagebuilder->get_block_attrs($block_name);
                } else {
                    $blocks[$block_name] = false;
                }
            }
        }

        return $blocks;
    }

    /**
     * Implementation of WP save_post action
     *
     * @param $post_id
     * @param $post
     *
     * @return mixed
     */
    static function save_post( $post_id, $post ) {
        /*if(!wp_verify_nonce($_POST['_wpnonce'])){
            return $post_id;
        }*/

	    if(!current_user_can( "edit_post", $post_id)){
		    return $post_id;
	    }

	    if(wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)){
		    return $post_id;
	    }

        if($post->post_type == "revision"){
            $pid = $post->post_parent;
        }else{
            $pid = $post_id;
        }

	    if(isset($_POST['pbcache'])){
		    $post_content = $post->post_content;
		    //$sections = get_post_meta($pid,self::$postmetas['sections'],true);
		    //if(!$sections || empty($sections)) $sections = array();
		    $sections = array();
		    foreach($_POST['pbcache'] as $editor_id => $editor_data){
			    $find_pregex = "/\[pagebuilder id=\"$editor_id\"\]/";
			    $find_pregex = preg_replace("/-/","\\-",$find_pregex);
			    if(!preg_match($find_pregex,$post_content)){
				    unset($_POST['pbcache'][$editor_id]);
				    continue;
			    }
			    $pbcontent = trim( preg_replace( "|[\r\n\t]|", "", $editor_data['content'] ) );
			    $generated = self::compile_pb_content( $pbcontent );
			    $new_pagebuilder_id = empty($sections) ? 0 : get_last_key($sections) + 1;
			    $sections[$new_pagebuilder_id] = array(
				    "content" => $generated,
				    "blocks" => $editor_data['blocks']
			    );
			    $post_content = preg_replace("/$editor_id/",$new_pagebuilder_id,$post_content);
		    }
		    update_post_meta($pid,self::$postmetas['sections'],$sections);
		    remove_action( 'save_post', '\WBF\modules\pagebuilder\Manager::save_post', 10 );
		    wp_update_post(array(
			    'ID' => $post_id,
			    'post_content' => $post_content
		    ));
		    add_action( 'save_post', '\WBF\modules\pagebuilder\Manager::save_post', 10, 2 );
	    }

        return $post_id;
    }

	/**
	 * Cleanup the content automatic markup injected by tmce between pagebuilder tags
	 * @param $content
	 * @uses Parser::clean_up_tmce_content()
	 *
	 * @return mixed|string
	 */
	static function clean_up_the_content_filter($content){
        global $post;

        if(!isset($post)){
            return $content;
        }

        $pbused = get_post_meta($post->ID,self::$postmetas['content'],true) != "" ? true : false; //todo: with the new "section" pagebuilder, we do not use $pbused anymore... so this function is useless?

        if($pbused){
            $parser = new Parser(get_current_builder(true));
            $content = $parser->clean_up_tmce_content($content,true);
        }

        return $content;
    }

    /**
     * Compile the page builder content to shortcoded version
     *
     * @param string $content the raw content
     *
     * @return string
     *
     * @uses Parser::_block_to_shortcode()
     */
    static function compile_pb_content($content = "") {
        //$content = wbpb_demo_content();
        $content = stripslashes($content);
        $dom = str_get_html($content);
        $blocks = $dom->find("[data-block]");
        $output = "";
        foreach ($dom->root->children as $node) {
            $output .= Parser::block_to_shortcode($node);
        }

        return $output;
    }

	/**
	 * Generate the page builder raw content from a shortcoded version
	 * @param $content
	 * @param bool $first_time
	 *
	 * @return mixed
	 */
	static function decompile_pb_content( $content, $first_time = false ) {
		$current_builder = get_current_builder( true );
		$parser = new Parser($current_builder);

		$content = $parser->clean_up_tmce_content($content); //Cleanup the content generated by TinyMCE

		if ( $first_time ) {
			$output = $current_builder->first_time( $content );
		} else {
			$output = $parser->tmce_to_pb($content);
		}

		return $output;
	}

    /**
     * Compile the page builder content to shortcoded version (called via ajax)
     * @uses Manager::compile_pb_content();
     */
    static function async_compile_pb_content() {
        $content = "";
        if (isset($_POST['content'])) {
            $content = $_POST['content'];
        }

        $compiled_content = Manager::compile_pb_content($content);

        /*$compiled_content = preg_replace('|\\"|',"",$compiled_content);
        $compiled_content = preg_replace('|\\|',"",$compiled_content);*/

        echo $compiled_content;
        die();
    }

	/**
	 * Generate the page builder raw content from a shortcoded version
	 */
	static function async_decompile_pb_content() {
        $content      = "";
        $already_used = 0;
        if ( isset( $_POST['content'] ) ) {
            $content = $_POST['content'];
        }
        if ( isset( $_POST['already_used'] ) ) {
            $already_used = $_POST['already_used'];
        }

        if ( $already_used == 0 ) {
            $pb_content = Manager::decompile_pb_content( $content, true );
        } else {
            $pb_content = Manager::decompile_pb_content( $content );
        }

        echo $pb_content;
        die();
    }

    /**
     * Functions called via ajax when an user clicks on an "Add {block}" tool.
     * @uses PageBuilder/get_block
     */
    static function get_block(){
        if(!isset($_POST['block_name'])){
            echo "";
        }else{
            $block_name = $_POST['block_name'];
            $current_editor = get_current_builder(true);
            $block['layout'] = $current_editor->get_block($block_name);
            $block['info'] = $current_editor->get_block_attrs($block_name);
            echo json_encode($block);
        }
        die();
    }

    /**
     * Retrieves the $block_name edit screen. Called via ajax when an user click on an "Edit" icon
     */
    static function get_block_edit_screen() {
        if ( ! isset( $_POST['block_name'] ) ) {
            echo "";
        } else {
            $block_name = $_POST['block_name'];
            $current_editor = get_current_builder( true );
            $generating_method = $block_name . "_edit";
            if ( method_exists( $current_editor, $generating_method ) ) {
                $output = PageBuilderTools::edit_screen($current_editor->$generating_method());
                echo $output;
            } else {
                echo "";
            }
        }
        die();
    }

	/**
	 * Register the shortcodes of current active pagebuilder (called during framework init)
	 */
	static function init_shortcodes(){
        if(!isset($GLOBALS['wbpagebuilder'])) return;

		//Register the [pagebuilder] shortcode
		add_shortcode("pagebuilder",'\WBF\modules\pagebuilder\Manager::pagebuilder_shortcode');

		//Register shortcodes of current active pagebuilder
        foreach($GLOBALS['wbpagebuilder']->blocks as $block_name => $block_attr){
            if(method_exists($GLOBALS['wbpagebuilder'],$block_name."_output")){
                add_shortcode($block_name,array($GLOBALS['wbpagebuilder'],$block_name."_output"));
            }
        }
    }

	/**
	 * Elaborate the [pagebuilder] shortcode.
	 * @param $atts
	 *
	 * @return string
	 */
	static function pagebuilder_shortcode($atts){
		global $post;
		if($post && $post->ID != 0 && isset($atts['id'])){
			$sections = get_post_meta($post->ID,Manager::$postmetas['sections'],true);
			if(!empty($sections) && isset($sections[$atts['id']])){
				$compiled_content = do_shortcode($sections[$atts['id']]['content']);
				return $compiled_content;
			}
		}
	}
}