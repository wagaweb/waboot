<?php

namespace WBF\modules\pagebuilder;

class PageBuilder {
    var $blocks = array();
    var $containers = array();
    var $widgets = array();
    var $toolbar = array();

    function __construct(){

    }

    /**
     * Parse block builder-specific tags among block builder-specific output
     * It is a part of the default usage for pagebuilder blocks.
     *
     * @param $block_name
     * @param $content
     * @param array $args
     *
     * @return mixed|string
     */
    function parse_block_content( $block_name, $content, $args = array() ) {
        $output = $content;
        //Replace "{class}"
        $output = $this->parse_block_tags( $block_name, "class", $output, $args );
        //Adding "data-" attributes
        $output = $this->parse_block_tags( $block_name, "data", $output, $args );
        //Adding tools
        $output = $this->parse_block_tags( $block_name, "tools", $output, $args );

        return $output;
    }

    /**
     * Replace pagebuilder block tags (eg: {class} {data}... ) with their respective data
     * @param $block_name
     * @param $tag
     * @param string $content
     * @param array $args
     *
     * @return mixed|string
     */
    function parse_block_tags( $block_name, $tag, $content = '', $args = array() ) {
        if ( preg_match( "/::([a-zA-Z_]+)/", $block_name, $matches ) ) {
            $block_name = $matches[1];
        }

        if ( $content == '' ) {
            if ( method_exists( $this, $block_name ) ) {
                $content = $this->$block_name( $args );
            } else {
                return $content;
            }
        }
        switch ( $tag ) {
            case "class":
                $content = preg_replace( "/{class}/", $this->get_block_class( $block_name ), $content );
                break;
            case "data":
                $blocks_attr               = $this->get_block_attrs( $block_name );
                $data_children_number_txt  = "data-children='0'";
                $data_max_children_txt     = $blocks_attr['max_children'] ? "data-max-children='{$blocks_attr['max_children']}'" : "";
                $data_max_children_row_txt = $blocks_attr['max_children_per_row'] ? "data-max-children-per-row='{$blocks_attr['max_children_per_row']}'" : "";
                $data_block_txt            = "data-block='$block_name'";
                $data_selectable_txt       = $blocks_attr['selectable'] ? 'data-selectable' : "";
                $data_sortable_txt         = $blocks_attr['sort'] ? "data-sortable='{$this->get_block_sorting_class( $block_name )}'" : "";
                $data_attr_txt             = $data_children_number_txt . " " . $data_max_children_txt . " " . $data_max_children_row_txt . " " . $data_block_txt . " " . $data_selectable_txt . " " . $data_sortable_txt;
                $content                   = preg_replace( "/{data}/", $data_attr_txt, $content );
                break;
            case "tools":
                $content = preg_replace( "/{tools}/", $this->get_block_menu( $block_name ), $content );
                break;
        }

        return $content;
    }

    /**
     * Get a "class" attribute of a block
     *
     * @param $block_name
     *
     * @return string
     */
    function get_block_class($block_name)
    {
        if (isset($this->blocks[$block_name])) {
            if (isset($this->blocks[$block_name]['class'])) {
                return $this->blocks[$block_name]['class'];
            }
        }

        return 'pbblock';
    }

    /**
     * Get all attributes of a block
     *
     * @param $block_name
     *
     * @return array
     */
    function get_block_attrs($block_name)
    {
        if (isset($this->blocks[$block_name])) {
            $attrs = $this->blocks[$block_name];

            $defaults = array(
              'selectable' => true,
              'sort' => false,
              'sortable' => false,
              'resize_children' => false,
              'max_children_per_row' => 0,
              'max_children' => false,
              'editable' => false,
              'resizable' => false,
              'preview' => false,
              'preview_from_field' => '',
              'preview_to' => ''
            );

            $attrs = wp_parse_args($attrs, $defaults);

            //Make the max_children_per_row an even number
            if ($attrs['max_children_per_row'] != 0 && $attrs['max_children_per_row'] % 2 != 0)
                $attrs['max_children_per_row']++;

            if (array_key_exists($block_name, $this->containers)) {
                $attrs['container'] = true;

                return array_merge($attrs, $this->containers[$block_name]);
            } elseif (array_key_exists($block_name, $this->widgets)) {
                $attrs['widget'] = true;

                return array_merge($attrs, $this->widgets[$block_name]);
            } else {
                return $attrs;
            }
        } else {
            return array();
        }
    }

    /**
     * Get which class a "sortable" div must allow to sort (eg: the "row" block that make the inner "column"s blocks sortable)
     *
     * @param $block_name
     *
     * @return mixed|string
     */
    function get_block_sorting_class($block_name)
    {
        if (isset($this->blocks[$block_name])) {
            if (isset($this->blocks[$block_name]['sort'])) {
                $element_to_sort = $this->blocks[$block_name]['sort'];
                if($element_to_sort == "*"){ //sort: *
                    return $element_to_sort;
                }
                if (preg_match("/[.#][a-zA-Z]+/", $element_to_sort) && !preg_match("/[,]+/", $element_to_sort)) { //sort .class
                    return $element_to_sort;
                } else {
                    if (preg_match("/[,]+/", $element_to_sort)) { //sort: blockname or blockname1,blockname2 or blockname,.classname,#idname
                        $elements_to_sort = explode(",",$element_to_sort);
                        $output = "";
                        $i = 1;
                        foreach($elements_to_sort as $block_or_selector){
                            if (array_key_exists($block_or_selector, $this->blocks)) {
                                $block = $block_or_selector;
                                if($i == count($elements_to_sort)){
                                    $output .= ".".$this->blocks[$block]['class'];
                                }else{
                                    $output .= ".".$this->blocks[$block]['class'].",";
                                }
                            }elseif(preg_match("/[.#][a-zA-Z]+/", $block_or_selector)){
                                $selector =  $block_or_selector;
                                if($i == count($elements_to_sort)){
                                    $output .= $selector;
                                }else{
                                    $output .= $selector.",";
                                }
                            }
                            $i++;
                        }
                        if($output != "") return $output;
                    }else{
                        if (array_key_exists($element_to_sort, $this->blocks)) {
                            if (isset($this->blocks[$element_to_sort]['class'])) {
                                return ".".$this->blocks[$element_to_sort]['class'];
                            }
                        }
                    }
                }
            }
        }

        return ".pbblock";
    }

    /**
     * Get the menu of a block
     *
     * @param $block_name
     *
     * @return string
     */
    function get_block_menu( $block_name ) {
        if ( isset( $this->blocks[ $block_name ] ) ) {
            $output = "<div class='tools'>";

            $output .= "<a class='label label-danger remove'><i class='fa fa-trash'></i></a>";

            $output .= "<a class='label label-danger clone'><i class='fa fa-files-o'></i></a>";

            if ( $this->block_is_sortable( $block_name ) ) {
                $output .= "<a class='label label-default drag'><i class='fa fa-arrows'></i></a>";
            }

            if ( $this->block_is_editable( $block_name ) )
                $output .= "<a class='label label-default edit'><i class='fa fa-pencil'></i></a>";

            if ($this->block_is_resizable($block_name)) {
                $output .= "<a class='label label-default resize' data-direction='left'><i class='fa fa-chevron-circle-left'></i></a>";
                $output .= "<a class='label label-default resize' data-direction='right'><i class='fa fa-chevron-circle-right'></i></a>";
            }

            $output .= "</div>";
            return $output;
        }else{
            return "";
        }
    }

    function block_is_sortable( $block_name ) {

        if(array_key_exists($block_name,$this->blocks) && isset($this->blocks[$block_name]['sortable']) && $this->blocks[$block_name]['sortable']){
            return true;
        }

        foreach ( $this->blocks as $name => $val ) {
            if(isset($val['sort'])){
                if (preg_match("/[,]+/", $val['sort'])) {
                    $elements_to_sort = explode(",",$val['sort']);
                    if(in_array($block_name,$elements_to_sort)){
                        return true;
                    }
                }else{
                    if ( $val['sort'] == $block_name ) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    function block_is_editable( $block_name ) {
        if ( array_key_exists( $block_name, $this->blocks ) ) {
            if ( isset( $this->blocks[ $block_name ]['editable'] ) ) {
                if ( $this->blocks[ $block_name ]['editable'] ) {
                    return true;
                }
            }
        }

        return false;
    }

    function block_is_resizable($block_name)
    {
        if (array_key_exists($block_name, $this->blocks)) {
            if (isset($this->blocks[$block_name]['resizable'])) {
                if ($this->blocks[$block_name]['resizable']) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Put the $content as a key into the $args array and parse the shortcodes in it.
     * It is a part of the default usage for pagebuilder blocks.
     * @param array $args
     * @param null $content
     *
     * @return array
     */
    function parse_block_args($args = array(), $content = null)
    {
        if (isset($args[0])) {
            //then we arrive from wordpress shortcode parser
            $i = 0;
            foreach ($args as $k => $arg) {
                $arg_name = preg_match("/^([a-zA-Z]+)='([a-zA-Z0-9]+)'/", $arg, $matches);
                if (isset($matches[1]) && isset($matches[2])) {
                    $args[$matches[1]] = $matches[2];
                    unset($args[$i]);
                }
                $i++;
            }
        }

        if (!isset($args['content'])) {
            $args['content'] = isset($content) ? do_shortcode($content) : null;
        } else {
            $args['content'] = do_shortcode($args['content']);
        }

        /*foreach($args as $k => $v){
            $args[$k] = stripslashes($v);
        }*/

        return $args;
    }

    /**
     * Get the block output for pagebuilder editor
     *
     * @param $block_name
     *
     * @return bool|mixed HTML or FALSE if the block method does not exists
     */
    function get_block( $block_name, $args = array() ) {
        if ( method_exists( $this, $block_name ) ) {
            if ( isset( $args['content'] ) ) {
                $output = $this->$block_name( $args, $args['content'] );
            } else {
                $output = $this->$block_name( $args );
            }
            return $output;
        } else {
            return false;
        }
    }
}