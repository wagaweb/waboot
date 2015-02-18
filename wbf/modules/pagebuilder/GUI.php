<?php

namespace WBF\modules\pagebuilder;

class GUI {
    static function init(){
        add_filter( 'media_buttons_context', '\WBF\modules\pagebuilder\GUI::insert_pb_button' );
        add_action( 'admin_footer', '\WBF\modules\pagebuilder\GUI::insert_editor_base', 12 );
	    add_filter( 'the_editor', '\WBF\modules\pagebuilder\GUI::editor'  );

	    //TINY MCE MODS
	    //add_filter( 'tiny_mce_before_init', '\WBF\admin\modules\pagebuilder\GUI::tinymce__settings' );
        //add_filter( 'mce_external_plugins', '\WBF\admin\modules\pagebuilder\GUI::tinymce_external_plugins' );

        //WP-VIEW scripts
        add_action( 'print_media_templates', '\WBF\modules\pagebuilder\GUI::shortcode_placeholder_tpl' );
    }

    /**
     * Implementation of WP the_editor filter
     * @param $content
     *
     * @return string
     */
    static function editor( $content ) {
        preg_match( "/<textarea[^>]*id=[\"']([^\"']+)\"/", $content, $matches );
        $id = $matches[1];
        // only for main content
        if ( $id !== "content" ) {
            return $content;
        }
        ob_start();

        return self::_editor_datas() . $content . ob_get_clean();
    }

    /**
     * Display editors working data (caches, ect.. ). Them are appended to WP Editor (see self::editor).
     */
    static function _editor_datas() {
        global $post;
        $builder = get_current_builder(true);
        $sections = get_post_meta($post->ID,Manager::$postmetas['sections'],true);
        ?>
        <div id="wb-pagebuilder-editors-data">
			<!-- Current editors data (appended via js) -->
	        <?php if($sections && !empty($sections)): foreach($sections as $section_id => $section_data) : ?>
		        <input type="hidden" id="<?php echo $section_id ?>-pbblocks" name="pbcache[<?php echo $section_id ?>][blocks]" value="{}" data-block-cache-for="<?php echo $section_id ?>">
		        <input type="hidden" id="<?php echo $section_id ?>-pbcontent" name="pbcache[<?php echo $section_id ?>][content]" value="<?php echo ""; ?>" data-content-cache-for="<?php echo $section_id ?>">
		    <?php endforeach; endif; ?>
        </div>
        <input type="hidden" id="base-pbcontent" name="pbcontent" value=''/>
        <input type="hidden" id="base-pbblocks" name="pbblocks" value='{}'/>
        <?php
    }

	/**
	 * Insert the "Add new page builder" button to WP Editor
	 * @param $context
	 *
	 * @return string
	 */
	static function insert_pb_button($context){
        global $pagenow;
        if ( in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) ) {
            $context .= '<a id="insert-wb-new-pagebuilder-placeholder" href="#" class="button" title="' . __( "Add a new page builder managed section to the current page", "wbf" ) . '"> ' . __( "Add Page Builder Section", "wbf" ) . '</a>';
            $context .= '<a style="display:none;" id="open-pagebuilder" href="#" class="thickbox button" title="' . __( "Add a new page builder managed section to the current page", "wbf" ) . '"> ' . __( "Add Page Builder Section", "wbf" ) . '</a>';
        }
        return $context;
    }

	/**
	 * Append the base editor and utility classes into admin footer.
	 * @param $context
	 */
	static function insert_editor_base($context){
        global $pagenow;
        // Only run in post/page creation and edit screens
        if(in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ))){
	        $builder = get_current_builder(true);
            $initial_content = $builder->first_time("...");
            ?>
	        <?php echo self::editor_frontend_tpl("wb-pagebuilder-base-modal","wb-pagebuilder-base",$initial_content); ?>
	        <?php
	        global $post;
	        $parser = new Parser($builder);
	        if($post && $post->ID != 0){
		        $sections = get_post_meta($post->ID,Manager::$postmetas['sections'],true);
	        }
	        ?>
	        <div id="wb-pagebuilder-editors" style="display: none;">
		        <?php if(isset($sections) && !empty($sections)) : foreach($sections as $sections_id => $section_data) : ?>
			        <?php echo self::editor_frontend_tpl($sections_id."-modal",$sections_id,$parser->tmce_to_pb($section_data['content'])); ?>
		        <?php endforeach; endif; ?>
	        </div>
	        <div class="wb-pagebuilder-loading" data-loading-window></div>
            <?php
        }
    }

    static function generate_toolbar() {
        global $wbpagebuilder;
        $toolbar = "";
        foreach ( $wbpagebuilder->toolbar as $label => $block_info ) {
            if(in_array($label,Manager::$special_menu_labels)){
                switch($label){
                    case '{menu}':
                        $toolbar .= "<select class='wb-pb-toolsmenu'><option value='label'>{$block_info['label']}</option>";
                        foreach($block_info['options'] as $block_label => $block_data){
                            $toolbar .= "<option class='pbtool' value='{$block_data['block']}' data-add='{$block_data['block']}'>{$block_label}</option>";
                        }
                        $toolbar .= "</select>";
                        break;
                    default:
                        continue;
                        break;
                }
            }else{
                $toolbar .= "<a href='#' class='button pbtool' data-add='{$block_info['block']}'><span class='wp-media-buttons-icon'></span>{$label}</a>";
            }
        }
        return $toolbar;
    }

	/**
	 * Outputs the editor template
	 *
	 * @param $id
	 * @param $content
	 *
	 * @return string
	 */
	static function editor_frontend_tpl($modal_id,$id,$content){
		$builder = get_current_builder(true);
		$toolbar = self::generate_toolbar();
        $original_id = $id;
		if((string)$id == "wb-pagebuilder-base"){
			$toolbar_id = "wb-pagebuilder-base-toolbar";
		}else{
			$toolbar_id = "wb-pagebuilder-{$original_id}-toolbar";
            $id = "wb-pagebuilder-{$original_id}";
		}
		ob_start();
		?>
		<div id="<?php echo $modal_id; ?>" <?php if($id == "wb-pagebuilder-base") echo 'style="display: none;"'; ?>>
            <div id="<?php echo $id; ?>" class="wb-pagebuilder-wrap bootstrap" data-editor="<?php echo $original_id; ?>">
                <div class="close-icon">
                    <a href="#" data-link-action="close">[CLOSE]</a>
                </div>
				<div id="<?php echo $toolbar_id ?>" class="wb-pagebuilder-tools" data-toolbar="<?php echo $original_id; ?>">
					<h1><?php _e("Available tools","wbf"); ?></h1>
					<?php echo $toolbar; ?>
				</div>
				<div class="wb-pagebuilder-title">
					<h1>
                        <?php _e("Section Builder","wbf"); ?>
                    </h1>
				</div>
				<div class="wb-pagebuilder-mainscreen" data-block="main-container" data-sortable="<?php echo $builder->get_block_sorting_class("main-container"); ?>" data-selectable>
					<?php echo $content; ?>
				</div>
                <div class="wb-pagebuilder-editscreen" style="display:none;" data-editscreen>
                    Edit! :)
                </div>
				<div class="wb-pagebuilder-footer">
					<a href="#" data-savepb class="button media-button button-primary button-large media-button-insert"><?php _e("Save","wbf") ?></a>
				</div>
			</div>
		</div>
		<?php
		return trim( preg_replace( "|[\r\n\t]|", "", ob_get_clean() ) );
	}

	/**
	 * Outputs the pagebuilder shortcode view inside the wordpress editor.
	 */
	static function shortcode_placeholder_tpl(){
		if ( ! isset( get_current_screen()->id ) || get_current_screen()->base != 'post' )
			return;
		?>
		<script type="text/html" id="tmpl-editor-pagebuilder">
			<div data-id="{{ data.id }}" class="mceItem mceNonEditable pagebuilder">
				<p>[pagebuilder id="{{ data.id }}"]</p>
			</div>
		</script>
	<?php
	}

	static function tinymce_settings($settings){
		//For further uses
		return $settings;
	}

	/**
	 * mce_external_plugins
	 * Adds our tinymce plugin
	 * @param  array $plugin_array
	 * @return array
	 */
	function tinymce_external_plugins( $plugin_array ) {
		//For further uses
		return $plugin_array;
	}
}