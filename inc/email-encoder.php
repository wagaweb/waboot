<?php
/**
 * Email Address Encoder.
 * This is the "Email address encoder" plugin by Till Krüss (www.tillkruess.com) integrated in Waboot theme.
 *
 * @package   Waboot
 * @author    Riccardo D'Angelo <me@riccardodangelo.com>
 * @license   GNU General Public License
 * @link      http://www.waga.it
 * @copyright 2014 Riccardo D'Angelo and WAGA.it
 */

if(!function_exists('eae_encode_emails')) :

if (!defined('WABOOT_EAE_FILTER_PRIORITY'))
    define('WABOOT_EAE_FILTER_PRIORITY', 1000);

/**
 * Register filters to encode exposed email addresses in
 * posts, pages, excerpts, comments and widgets.
 */
foreach (array('the_content', 'the_excerpt', 'widget_text', 'comment_text', 'comment_excerpt') as $filter) {
    add_filter($filter, 'waboot_eae_encode_emails', WABOOT_EAE_FILTER_PRIORITY);
}

/**
 * Searches for plain email addresses in given $string and
 * encodes them (by default) with the help of eae_encode_str().
 *
 * Regular expression is based on based on John Gruber's Markdown.
 * http://daringfireball.net/projects/markdown/
 *
 * @param string $string Text with email addresses to encode
 * @return string $string Given text with encoded email addresses
 */
function waboot_eae_encode_emails($string) {

    // abort if $string doesn't contain a @-sign
    if (apply_filters('waboot_eae_at_sign_check', true)) {
        if (strpos($string, '@') === false) return $string;
    }

    // override encoding function with the 'eae_method' filter
    $method = apply_filters('waboot_eae_method', 'waboot_eae_encode_str');

    // override regex pattern with the 'eae_regexp' filter
    $regexp = apply_filters(
        'waboot_eae_regexp',
        '{
            (?:mailto:)?
            (?:
                [-!#$%&*+/=?^_`.{|}~\w\x80-\xFF]+
            |
                ".*?"
            )
            \@
            (?:
                [-a-z0-9\x80-\xFF]+(\.[-a-z0-9\x80-\xFF]+)*\.[a-z]+
            |
                \[[\d.a-fA-F:]+\]
            )
        }xi'
    );

    return preg_replace_callback(
        $regexp,
        create_function(
            '$matches',
            'return '.$method.'($matches[0]);'
        ),
        $string
    );

}

/**
 * Encodes each character of the given string as either a decimal
 * or hexadecimal entity, in the hopes of foiling most email address
 * harvesting bots.
 *
 * Based on Michel Fortin's PHP Markdown:
 *   http://michelf.com/projects/php-markdown/
 * Which is based on John Gruber's original Markdown:
 *   http://daringfireball.net/projects/markdown/
 * Whose code is based on a filter by Matthew Wickline, posted to
 * the BBEdit-Talk with some optimizations by Milian Wolff.
 *
 * @param string $string Text with email addresses to encode
 * @return string $string Given text with encoded email addresses
 */
function waboot_eae_encode_str($string) {

    $chars = str_split($string);
    $seed = mt_rand(0, (int) abs(crc32($string) / strlen($string)));

    foreach ($chars as $key => $char) {

        $ord = ord($char);

        if ($ord < 128) { // ignore non-ascii chars

            $r = ($seed * (1 + $key)) % 100; // pseudo "random function"

            if ($r > 60 && $char != '@') ; // plain character (not encoded), if not @-sign
            else if ($r < 45) $chars[$key] = '&#x'.dechex($ord).';'; // hexadecimal
            else $chars[$key] = '&#'.$ord.';'; // decimal (ascii)

        }

    }

    return implode('', $chars);

}

endif;

