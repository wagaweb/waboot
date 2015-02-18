<?php

namespace WBF\modules\pagebuilder;

/**
 * todo: questa classe potrebbe andare a sostituire il meccanismo di sovrascrizone\utilizzo degli shortcode di WP
 * Class PageBuilderParser
 * @package WBF\admin\Pagebuilder
 */
class Parser {
    var $shortcode_tags = array();
    var $blocks;
    var $pb;

    function __construct(PageBuilder $builder){
        $this->pb = $builder;
        $this->blocks = $builder->blocks;
    }

    /**
     * todo: not used ATM
     * @param $tag
     * @param $func
     */
    function add_shortcode( $tag, $func ) {
        //if ( is_callable($func) )
        $this->shortcode_tags[ $tag ] = $func;
    }

    /**
     * todo: not used ATM
     * @param $tag
     */
    function remove_shortcode( $tag ) {
        unset( $this->shortcode_tags[ $tag ] );
    }

    /**
     * todo: not used ATM
     */
    function remove_all_shortcodes() {
        $this->shortcode_tags = array();
    }

    /**
     * todo: not used ATM
     * @param $content
     * @param $tag
     *
     * @return bool
     */
    function has_shortcode( $content, $tag ) {
        if ( false === strpos( $content, '[' ) ) {
            return false;
        }

        if ( $this->shortcode_exists( $tag ) ) {
            preg_match_all( '/' . get_shortcode_regex() . '/s', $content, $matches, PREG_SET_ORDER );
            if ( empty( $matches ) ) {
                return false;
            }

            foreach ( $matches as $shortcode ) {
                if ( $tag === $shortcode[2] ) {
                    return true;
                } elseif ( ! empty( $shortcode[5] ) && $this->has_shortcode( $shortcode[5], $tag ) ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * todo: not used ATM
     * @param $tag
     *
     * @return bool
     */
    function shortcode_exists( $tag ) {
        return array_key_exists( $tag, $this->shortcode_tags );
    }

	/**
	 * Transform a raw html block node to a shortcode
	 *
	 * @param \simple_html_dom_node $block_node
	 *
	 * @return string
	 */
	static function block_to_shortcode(\simple_html_dom_node $block_node) {
		if (!isset($block_node->attr['data-block'])) {
			return "";
		}
		$block_name = $block_node->attr['data-block'];
		$options_output = "";
		$options_content = "";

		if (isset($block_node->attr['data-options']) && $block_node->attr['data-options'] != "") {
			$options = json_decode(str_replace("&quot;", '"', $block_node->attr['data-options']), true);

			foreach ($options as $name => $value) {
				if ($name == "content") {
					$options_content = /*"\n" . */$value/* . "\n"*/; //eg: [block]the content[/block]
					continue;
				};
				$options_output .= " $name='$value'"; //eg: id='column-2' or title='the title'
			}
		}

		if (isset ($block_node->attr['id']) && $block_node->attr['id'] != "") {
			$options_output .= " id='" . $block_node->attr['id'] . "'";
		}

		if (isset ($block_node->attr['data-colspan']) && $block_node->attr['data-colspan'] != "") {
			$options_output .= " colspan='" . $block_node->attr['data-colspan'] . "'";
		}

		$output = "[{$block_name}{$options_output}]{$options_content}";
		if ($block_node->has_child()) {
			foreach ($block_node->children as $child_node) {
				$output .= self::block_to_shortcode($child_node);
			}
		}
		//$output .= "[/$block_name]\n";
		$output .= "[/$block_name]";

		return $output;
	}

    /**
     * Cleanup the content automatic markup injected by tmce between pagebuilder tags
     * @param $content
     *
     * @return mixed|string
     */
    function clean_up_tmce_content($content,$clean_up_newlines = false){
        $content = stripslashes( $content );
        $content = preg_replace("/^<p>/","",$content); //cleanup <p> tag at the beginning of the string
        $content = preg_replace("/<\/p>$/","",$content); //...and at the end
        $regexp = "";
        $i = 0;
        foreach($this->blocks as $name => $val){
            $i++;
            if($name == "main-container") continue;
            if($i < count($this->blocks))
                $regexp .= "$name|";
            else
                $regexp .= "$name";
        }

        $br_regexp = "/(\[\/[$regexp]+\])<br> ?/";
        $content = preg_replace($br_regexp,"$1",$content); //replace <br> after closing tags

        $closing_p_regexp = "/(\[[$regexp]+)([a-zA-Z0-9='\- ]+)([\]])<\/p>/";
        $content = preg_replace($closing_p_regexp,"$1$2$3",$content); //replace </p> after tag opening

        if($clean_up_newlines){
            $newlines_regexp = "/(\[[$regexp]+)([a-zA-Z0-9='\- ]+)([\]])\n/";
            $content = preg_replace($newlines_regexp,"$1$2$3",$content); //replace \n after tag opening
        }

        $opening_p_regexp = "/<p>(\[\/[$regexp]+\])/";
        $content = preg_replace($opening_p_regexp,"$1",$content); //replace <p> before tag closing

        return $content;
    }


	/**
	 * Generate the page builder raw content from a shortcoded version
	 * @param $content
	 *
	 * @return string
	 */
	function tmce_to_pb($content){
        global $shortcode_tags;
        //Temporary clean up WP shortcodes
        $shortcode_tags_backup = $shortcode_tags;
        remove_all_shortcodes();
        //Fill up wp shortcodes with our shortcodes
        foreach ( $this->blocks as $name => $val ) {
            if ( $name == "main-container" ) {
                continue;
            }
            $tag = $name;
            $callback = array( $this->pb, "{$name}" ); //$current_builder->$name();
            add_shortcode( $tag, $callback );
        }
        $output = do_shortcode( $content );
        $shortcode_tags = $shortcode_tags_backup; //restore the backup

        return $output;
    }
}