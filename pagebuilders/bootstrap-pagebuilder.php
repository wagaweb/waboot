<?php

class BootstrapPageBuilder extends \WBF\modules\pagebuilder\PageBuilder {

	function __construct(){
		$this->blocks = array(
			'main-container' => array(
				'selectable'      => true,
				'sort'            => 'wbrow',
			),
			'wbdiv' => array(
				'selectable' => true,
				'class' => 'wbdiv',
				'sort' => 'wbrow,wbcolumn', //here we want to sort multiple blocks
				'editable' => 'true'
			),
			'wbrow'            => array(
				'selectable'           => true,
				'class'                => 'wbrow',  //the class attribute for this element
				'sort'                 => 'wbcolumn', //which element this block has to sort?
				'max_children_per_row' => 4,        //used to tell how many children can be side-by-side
                'editable' => true
			),
			'wbcolumn'         => array(
				'selectable'      => true,
				'class'           => 'wbcolumn',
				'sort' => '.widget', //here we want to sort a class not a block
				'resizable' => true,
                'editable' => true
			),
			'wbtext'           => array(
				'selectable' => false,
				'class'      => 'wbhtml',
				'sort'       => false,
				'sortable'   => true, //force sortable flag
				'editable' => true,
                'preview' => true,
                'preview_from_field' => 'content',
                'preview_to' => '.wbpbpreview',
			),
            'wbrecent_posts_widget' => array(
                'class'      => 'wbrecentposts',
                'sortable'   => true //force sortable flag
            )
		);

		$this->containers = array(
			'main-container' => array(
				'max_children' => 1
			),
			'wbrow' => array(
				//'max_children' => 4
			),
			'wbcolumn' => array(
				//'max_children' => 1
			)
		);

		$this->widgets = array(
			'wbtext' => array(
				'edit_type' => 'textarea'
			)
		);

		$this->toolbar = array(
			'Add div' => array(
				'block' => 'wbdiv',
				'enabled_on' => 'main-container,wbrow,wbcolumn'
			),
			'Add row'    => array(
				'block'      => 'wbrow',
				'enabled_on' => 'main-container,wbdiv'
			),
			'Add column' => array(
				'block'      => 'wbcolumn',
				'enabled_on' => 'wbrow,wbdiv'
			),
			'{menu}' => array(
				'label' => 'Add widgets',
				'options' => array(
					'Add Text block' => array(
						'block' => 'wbtext',
						'enabled_on' => 'wbcolumn'
					),
                    'Add recent posts widget' => array(
                        'block' => 'wbrecent_posts_widget',
                        'enabled_on' => 'wbcolumn'
                    )
				)
			)
		);
	}

	/****
	 *
	 *
	 * DIV BLOCK
	 *
	 *
	 *****/

	function wbdiv( $args = array(), $content = null) {
		$args = $this->parse_block_args( $args, $content );
		if ( isset( $args['content'] ) ) {
			$options = array();
			if(isset($args['extraclasses'])) $options['extraclasses'] = $args['extraclasses'];
			$options_json = !empty($options)? "data-options='".\WBF\modules\pagebuilder\wbpb_json_encode( $options )."'" : "";

			return $this->parse_block_content( __METHOD__, "<div class='{class}' {data} {$options_json}>{tools}{$args['content']}</div>" );
		}else{
			return $this->parse_block_content( __METHOD__, "<div class='{class}' {data}>{tools}</div>" );
		}
	}

	function wbdiv_edit(){
		$output = "<label for='extraclasses' >" . __("Extra classes", "waboot") . "</label><p>".__("Input extra classes for the div","waboot")."</p><input type='text' class='pb-modal-input' data-save='true' placeholder='' name='extraclasses' />";
		return $output;
	}

	function wbdiv_output($atts, $content = ""){
		$atts = shortcode_atts(array(
			'extraclasses' => ''
		),$atts);

		return "<div class='{$atts['extraclasses']}'>".do_shortcode($content)."</div>";
	}

	/****
	 *
	 *
	 * ROW BLOCK
	 *
	 *
	 *****/

	function wbrow( $args = array(), $content = null ) {
		$args = $this->parse_block_args( $args, $content );

		if ( isset( $args['content'] ) ) {
			$options = array();
			if(isset($args['extraclasses'])) $options['extraclasses'] = $args['extraclasses'];
			$options_json = !empty($options)? "data-options='".\WBF\modules\pagebuilder\wbpb_json_encode( $options )."'" : "";

			return $this->parse_block_content( __METHOD__, "<div class='{class}' {data} {$options_json}>{tools}{$args['content']}</div>" );
		}

		return $this->parse_block_content( __METHOD__, "<div class='{class}' {data}>{tools}</div>" );
	}

    function wbrow_edit(){
        $output = "<label for='extraclasses' >" . __("Extra classes", "waboot") . "</label><p>".__("Input extra classes for the row","waboot")."</p><input type='text' class='pb-modal-input' data-save='true' placeholder='' name='extraclasses' />";
        return $output;
    }

	function wbrow_output($atts, $content = ""){
        $atts = shortcode_atts(array(
            'extraclasses' => ''
        ),$atts);

		return "<div class='row {$atts['extraclasses']}'>".do_shortcode($content)."</div>";
	}

	/****
	 *
	 *
	 * COLUMN BLOCK
	 *
	 *
	 *****/

	function wbcolumn( $args = array(), $content = null ) {
		$args = $this->parse_block_args( $args, $content );

		if ( isset( $args['content'] ) ) {
			$options = array();
			$colspan = isset( $args['colspan'] ) ? "data-colspan='{$args['colspan']}'" : "";
			if(isset($args['extraclasses'])) $options['extraclasses'] = $args['extraclasses'];
			$options_json = !empty($options)? "data-options='".\WBF\modules\pagebuilder\wbpb_json_encode( $options )."'" : "";

			return $this->parse_block_content( __METHOD__, "<div class='{class}' {data} {$colspan} {$options_json}>{tools}{$args['content']}</div>" );
		}

		return $this->parse_block_content( __METHOD__, "<div class='{class}' {data}>{tools}</div>" );
    }

    function wbcolumn_edit(){
        $output = "<label for='extraclasses' >" . __("Extra classes", "waboot") . "</label><p>".__("Input extra classes for the column","waboot")."</p><input type='text' class='pb-modal-input' data-save='true' placeholder='' name='extraclasses' />";
        return $output;
    }

	function wbcolumn_output($atts, $content = ""){
		$atts = shortcode_atts(array(
			'colspan' => 4,
            'extraclasses' => ''
		),$atts);

		switch($atts['colspan']){
			case 4:
				$class = "col-sm-12";
				break;
			case 3:
				$class = "col-sm-7";
				break;
			case 2:
				$class = "col-sm-6";
				break;
			case 1:
				$class = "col-sm-3";
				break;
			default:
				$class = "col-sm-12";
				break;
		}

		return "<div class='{$class} {$atts['extraclasses']}'>".do_shortcode($content)."</div>";
	}

	/****
	 *
	 *
	 * TEXT BLOCK
	 *
	 *
	 *****/

	function wbtext( $args = array(), $content = null ) {
		$args = $this->parse_block_args( $args, $content );

		if ( ! empty( $args ) ) {
			$options = array(
				'content' => isset( $args['content'] ) ? $args['content'] : ""
			);
			$options_json = \WBF\modules\pagebuilder\wbpb_json_encode( $options );
            $preview = isset( $args['content'] ) ? \WBF\modules\pagebuilder\PageBuilderTools::create_excerpt($args['content']) : "HTML";

			return $this->parse_block_content( "wbtext", "<div class='wbelement {class} widget' {data} data-options='" . $options_json . "'>{tools}<span class='wbpbpreview'>{$preview}</span></div>" );
		}

		return $this->parse_block_content( "wbtext", "<div class='wbelement {class} widget' {data}>{tools}HTML</div>" );
	}

	function wbtext_edit() {
        $output = \WBF\modules\pagebuilder\PageBuilderTools::tinymce_editor();
		return $output;
	}

	function wbtext_output( $atts, $content = "" ) {
		return "<div class='wbtext'>".do_shortcode($content)."</div>";
	}

	/****
	 *
	 *
	 * RECENTPOSTS BLOCK
	 *
	 *
	 *****/

    function wbrecent_posts_widget($args = array(), $content = null){
        $args = $this->parse_block_args( $args, $content );

        return $this->parse_block_content( __METHOD__, "<div class='wbelement {class} widget' {data}>{tools}RECENT POSTS</div>" );
    }

    function wbrecent_posts_widget_output($atts, $content){
        ob_start();
        the_widget( 'WP_Widget_Recent_Posts' );
        $output = ob_get_clean();
        return $output;
    }

    /** FIRT TIME METHOD **/

	function first_time( $content ) {
		return $this->get_block( "wbrow", array(
				"content" => $this->get_block( "wbcolumn", array(
						"content" => $this->get_block( "wbtext", array( "content" => $content ) ),
						"colspan" => 4
					)
				)
			)
		);
	}
}