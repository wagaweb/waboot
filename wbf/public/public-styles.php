<?php
/**
 * Register and enqueue the front end CSS
 *
 * @package Waboot
 * @since 0.1.0
 */

add_action( 'wp_enqueue_scripts', 'wbf_add_client_custom_css', 99 );
add_action( 'wp_enqueue_scripts', 'wbf_options_typography_google_fonts' );
add_action('wp_head', 'options_typography_primary_styles');
add_action('wp_head', 'options_typography_secondary_styles');
//add_action("waboot_head", 'waboot_theme_options_header_styles');

/**
 * Adds client custom CSS
 */
function wbf_add_client_custom_css(){
	$client_custom_css = waboot_of_custom_css();

	if($client_custom_css){
		wp_enqueue_style('client-custom',$client_custom_css);
	}
}

/**
 * Checks font options to see if a Google font is selected.
 * If so, options_typography_enqueue_google_font is called to enqueue the font.
 * Ensures that each Google font is only enqueued once.
 */
if ( !function_exists( 'wbf_options_typography_google_fonts' ) ) {
    function wbf_options_typography_google_fonts() {
        $all_google_fonts = array_keys( options_typography_get_google_fonts() );
        // Define all the options that possibly have a unique Google font
          $primary_font = of_get_option('waboot_primary_font', 'Lato, sans-serif');
          $secondary_font = of_get_option('waboot_secondary_font', false);
        // $google_mixed_2 = of_get_option('google_mixed_2', 'Arvo, serif');
        // Get the font face for each option and put it in an array
        $selected_fonts = array(
            $primary_font['face'],
            $secondary_font['face']);
        // Remove any duplicates in the list
        $selected_fonts = array_unique($selected_fonts);
        // Check each of the unique fonts against the defined Google fonts
        // If it is a Google font, go ahead and call the function to enqueue it
        foreach ( $selected_fonts as $font ) {
            if ( in_array( $font, $all_google_fonts ) ) {
                options_typography_enqueue_google_font($font);
            }
        }
    }
}

/**
 * Enqueues the Google $font that is passed
 */
function options_typography_enqueue_google_font($font) {
    $font = explode(',', $font);
    $font = $font[0];
    // Certain Google fonts need slight tweaks in order to load properly
    // Like our friend "Raleway"
    if ( $font == 'Raleway' )
        $font = 'Raleway:100';
    $font = str_replace(" ", "+", $font);
    wp_enqueue_style( "options_typography_$font", "//fonts.googleapis.com/css?family=$font", false, null, 'all' );
}

/**
 * Outputs the selected option panel styles inline into the <head>
 */

/** Primary Font Family */
function options_typography_primary_styles() {
    $output = '';
    $input = '';

    if ( of_get_option( 'waboot_primary_font' ) ) {
        $input = of_get_option( 'waboot_primary_font' );
        $output .= options_typography_font_styles( of_get_option( 'waboot_primary_font' ) , 'body, p, ul, li');
    }

    if ( $output != '' ) {
        $output = "\n<style>\n" . $output . "</style>\n";
        echo $output;
    }
}

/** Secondary Font Family */
function options_typography_secondary_styles() {
    $output = '';
    $input = '';

    if ( of_get_option( 'waboot_secondary_font' ) ) {
        $input = of_get_option( 'waboot_secondary_font' );
        $output .= options_typography_font_styles( of_get_option( 'waboot_secondary_font' ) , 'h1, h2, h3, h4, h5, h6');
    }

    if ( $output != '' ) {
        $output = "\n<style>\n" . $output . "</style>\n";
        echo $output;
    }
}

/**
 * Returns a typography option in a format that can be outputted as inline CSS
 */
function options_typography_font_styles($option, $selectors) {
    $output = $selectors . ' {';
    $output .= ' color:' . $option['color'] .'; ';
    $output .= 'font-family:' . $option['face'] . '; ';
    $output .= 'font-weight:' . $option['weight'] . '; ';
    $output .= 'font-style:' . $option['fstyle'] . '; ';
   // $output .= 'font-size:' . $option['size'] . '; ';
    $output .= '}';
    $output .= "\n";
    return $output;
}

/**
 * Apply custom in-line styles in header
 * @deprecated
 */
function waboot_theme_options_header_styles(){
	?>
	<style type="text/css">
		body {
			background-color: <?php echo of_get_option( 'waboot_body_bgcolor' ); ?> !important;
			background-image: url(<?php echo of_get_option( 'waboot_body_bgimage' ); ?>);
			background-repeat: <?php echo of_get_option( 'waboot_body_bgrepeat' ); ?>;
			background-position: <?php echo of_get_option( 'waboot_body_bgpos' ); ?>;
			background-attachment: <?php echo of_get_option( 'waboot_body_bgattach' ); ?>;
		}

		#topnav-wrapper {
			background-color: <?php echo of_get_option( 'waboot_topnav_bgcolor' ); ?>;
		}

		#header-wrapper {
			background-color: <?php echo of_get_option( 'waboot_header_bgcolor' ); ?>;
		}

		#banner-wrapper {
			background-color: <?php echo of_get_option( 'waboot_banner_bgcolor' ); ?>;
		}

		#content-wrapper {
			background-color: <?php echo of_get_option( 'waboot_content_bgcolor' ); ?>;
		}

		#contentbottom-wrapper {
			background-color: <?php echo of_get_option( 'waboot_bottom_bgcolor' ); ?>;
		}

		#footer-wrapper {
			background-color: <?php echo of_get_option( 'waboot_footer_bgcolor' ); ?>;
		}

		#page {
			background-color: <?php echo of_get_option( 'waboot_page_bgcolor' ); ?>;
		}

		.navbar.main-navigation .navbar-collapse {
			background-color: <?php echo of_get_option( 'waboot_navbar_bgcolor' ); ?>;
		}

	</style>
<?php
}