<?php

namespace WBF\modules\pagebuilder;

class PageBuilderTools {

    static function edit_screen($include = "") {
        $output = "<div class='wb-pb-editscreen-content'>{$include}</div>
        <div class='wb-pb-editscreen--footer'>
            <a href='#' role='button' data-link-action='submit-edit' class='submit-edit button button-primary button-large'>" . __( "Save settings", "wbf" ) . "</a>
            <a href='#' role='button' data-link-action='close-edit' class='close-edit button button-secondary button-large'>" . __( "Cancel", "wbf" ) . "</a>
        </div>";
        return $output;
    }

    static function simple_editor($default = "", $placeholder = "") {
        $rand_id = "textarea-" . rand();
        $output = "<label for='content' >" . __("Content", "waboot") . "</label><textarea id='{$rand_id}' class='pb-modal-input' data-save='true' name='content' placeholder='{$placeholder}'>{$default}</textarea>";
        return $output;
    }

    static function tinymce_editor($default = "", $textarea_name = "content" ,$placeholder = "") {
        $rand_id = self::rand_id("tmce", 5, true);

        ob_start();
        wp_editor($default, $rand_id, array(
          'textarea_name' => $textarea_name,
          'tinymce' => array(
	          'theme_advanced_buttons1' => 'formatselect,|,bold,italic,underline,|,' .
              'bullist,blockquote,|,justifyleft,justifycenter' .
              ',justifyright,justifyfull,|,link,unlink,|' .
              ',spellchecker,wp_fullscreen,wp_adv'
          )
        ));
        $editor_content = ob_get_clean();
        $editor_content = preg_replace("/<textarea class/", "<textarea data-is-tmce='true' data-save='true' class", $editor_content);
        $output = $editor_content;

        return $output;
    }

    static function rand_id($suffix, $len = 5, $only_letters = false) {
        if ($only_letters) {
            $charset = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        } else {
            $charset = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        }

        $base = strlen($charset);
        $result = '';

        $now = explode(' ', microtime());
        $now = $now[1];
        while ($now >= $base) {
            $i = $now % $base;
            $result = $charset[$i] . $result;
            $now /= $base;
        }
        return $suffix . "_" . substr($result, -5);
    }

    static function create_excerpt($text = null, $lenght = 256){
        $doing_ajax = false;
        if(isset($_POST['text'])){
            $doing_ajax = true;
            $text = stripslashes($_POST['text']);
        }

        if(isset($text)){
            $text = strip_shortcodes( $text ); // Strip shortcodes
            $text = apply_filters( 'the_content', $text );
            $text = str_replace(']]>', ']]&gt;', $text); // From the default wp_trim_excerpt() (Some kind of precaution against malformed CDATA in RSS feeds)
            //$text = wp_trim_words( $text, $lenght);
            //Strip tags
            $allowed_tags = array("img","p","h1","h2","h3","h4","h5","h6","ul","li","ol","strong","b","em","i");
            if ( count( $allowed_tags ) > 0 ) {
                $tag_string = '<' . implode( '><', $allowed_tags ) . '>';
            } else {
                $tag_string = '';
            }
            $text = strip_tags( $text, $tag_string );
            //Create the excerpt
            $text = self::text_excerpt( $text, $lenght);
            //$text .= __( '&hellip;' );
            if($doing_ajax){
                echo $text;
                die();
            }
            return $text;
        }

        if($doing_ajax){
            echo 0;
            die();
        }
        return false;
    }

    private static function text_excerpt( $text, $length, $length_type = "words", $finish = "exact" ){
        $tokens = array();
        $out = '';
        $w = 0;
        // Divide the string into tokens; HTML tags, or words, followed by any whitespace
        // (<[^>]+>|[^<>\s]+\s*)
        preg_match_all( '/(<[^>]+>|[^<>\s]+)\s*/u', $text, $tokens );
        foreach ( $tokens[0] as $t ) { // Parse each token
            if ( $w >= $length && 'sentence' != $finish ) { // Limit reached
                break;
            }
            if ( $t[0] != '<' ) { // Token is not a tag
                if ( $w >= $length && 'sentence' == $finish && preg_match( '/[\?\.\!]\s*$/uS', $t ) == 1 ) { // Limit reached, continue until ? . or ! occur at the end
                    $out .= trim( $t );
                    break;
                }
                if ( 'words' == $length_type ) { // Count words
                    $w++;
                } else { // Count/trim characters
                    $chars = trim( $t ); // Remove surrounding space
                    $c = strlen( $chars );
                    if ( $c + $w > $length && 'sentence' != $finish ) { // Token is too long
                        $c = ( 'word' == $finish ) ? $c : $length - $w; // Keep token to finish word
                        $t = substr( $t, 0, $c );
                    }
                    $w += $c;
                }
            }
            // Append what's left of the token
            $out .= $t;
        }
        return trim( force_balance_tags( $out ) );
    }
}