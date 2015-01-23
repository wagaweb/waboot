<?php
if (!function_exists( 'wbf_locate_template_uri' )):
    /**
     * Retrieve the URI of the highest priority template file that exists.
     *
     * Searches in the stylesheet directory before the template directory so themes
     * which inherit from a parent theme can just override one file.
     *
     * @param string|array $template_names Template file(s) to search for, in order.
     * @return string The URI of the file if one is located.
     */
    function wbf_locate_template_uri($template_names){
        $located = '';
        foreach ((array)$template_names as $template_name) {
            if (!$template_name)
                continue;

            if (file_exists(get_stylesheet_directory() . '/' . $template_name)) {
                $located = get_stylesheet_directory_uri() . '/' . $template_name;
                break;
            } else if (file_exists(get_template_directory() . '/' . $template_name)) {
                $located = get_template_directory_uri() . '/' . $template_name;
                break;
            }
        }

        return $located;
    }
endif;

if (!function_exists("waboot_admin_show_message")) :
    function waboot_admin_show_message($m, $type) {
        ?>
        <div class="<?php echo $type; ?>">
            <p><?php echo $m; ?></p>
        </div>
    <?php
    }
endif;

/***************************************************************
 * MOBILE DETECT FUNCTIONS
 ***************************************************************/

if (!function_exists("wb_is_mobile")):
    function wb_is_mobile()
    {
        $md = WBF::get_mobile_detect();
        return ($md->isMobile());
    }
endif;

if (!function_exists("wb_is_tablet")):
    function wb_is_tablet()
    {
        $md = WBF::get_mobile_detect();
        return ($md->isTablet());
    }
endif;

if (!function_exists("wb_is_ios")):
    function wb_is_ios()
    {
        $md = WBF::get_mobile_detect();
        return ($md->isiOS());
    }
endif;

if (!function_exists("wb_is_android")):
    function wb_is_android()
    {
        $md = WBF::get_mobile_detect();
        return ($md->isAndroidOS());
    }
endif;

if (!function_exists("wb_is_windows_mobile")):
    function wb_is_windows_mobile()
    {
        $md = WBF::get_mobile_detect();
        return ($md->is('WindowsMobileOS') || $md->is('WindowsPhoneOS'));
    }
endif;

if (!function_exists("wb_is_iphone")):
    function wb_is_iphone()
    {
        $md = WBF::get_mobile_detect();
        return ($md->isIphone());
    }
endif;

if (!function_exists("wb_is_ipad")):
    function is_ipad()
    {
        $md = WBF::get_mobile_detect();
        return ($md->isIpad());
    }
endif;

if (!function_exists("wb_is_samsung")):
    function wb_is_samsung()
    {
        $md = WBF::get_mobile_detect();
        return ($md->is('Samsung'));
    }
endif;

if (!function_exists("wb_is_samsung_tablet")):
    function wb_is_samsung_tablet()
    {
        $md = WBF::get_mobile_detect();
        return ($md->is('SamsungTablet'));
    }
endif;

if (!function_exists("wb_is_kindle")):
    function wb_is_kindle()
    {
        $md = WBF::get_mobile_detect();
        return ($md->is('Kindle'));
    }
endif;

if (!function_exists("wb_android_version")):
    function wb_android_version()
    {
        $md = WBF::get_mobile_detect();
        return $md->version('Android');
    }
endif;

if (!function_exists("wb_iphone_version")):
    function wb_iphone_version()
    {
        $md = WBF::get_mobile_detect();
        return $md->version('iPhone');
    }
endif;

if (!function_exists("wb_ipad_version")):
    function wb_ipad_version()
    {
        $md = WBF::get_mobile_detect();
        return $md->version('iPad');
    }
endif;

/***************************************************************
 * TYPOGRAPHY (these functions are deprecated)
 ***************************************************************/

/**
 * Returns an array of system fonts
 * Feel free to edit this, update the font fallbacks, etc.
 * @deprecated
 */
function options_typography_get_os_fonts() {
    // OS Font Defaults
    $os_faces = array(
        'Arial, sans-serif' => 'Arial',
        '"Avant Garde", sans-serif' => 'Avant Garde',
        'Cambria, Georgia, serif' => 'Cambria',
        'Copse, sans-serif' => 'Copse',
        'Garamond, "Hoefler Text", Times New Roman, Times, serif' => 'Garamond',
        'Georgia, serif' => 'Georgia',
        '"Helvetica Neue", Helvetica, sans-serif' => 'Helvetica Neue',
        'Tahoma, Geneva, sans-serif' => 'Tahoma'
    );
    return $os_faces;
}

/**
 * Returns a select list of Google fonts
 * Feel free to edit this, update the fallbacks, etc.
 * @deprecated
 */
function options_typography_get_google_fonts() {
    // Google Font Defaults
    $google_faces = array(
        '' => 'Select',
        'Abril Fatface, serif' => 'Abril Fatface',
        'Actor, sans-serif' => 'Actor',
        'Amaranth, sans-serif' => 'Amaranth',
        'Arvo, serif' => 'Arvo',
        'Average, sans-serif' => 'Average',
        'Bevan, serif' => 'Bevan',
        'Copse, sans-serif' => 'Copse',
        'Crimson Text, serif' => 'Crimson Text',
        'Dancing Script, cursive' => 'Dancing Script',
        'Droid Sans, sans-serif' => 'Droid Sans',
        'Droid Serif, serif' => 'Droid Serif',
        'EB Garamond, serif' => 'EB Garamond',
        'Exo, sans-serif' => 'Exo',
        'Exo 2, sans-serif' => 'Exo 2',
        'Fjord, serif' => 'Fjord',
        'Forum, serif' => 'Forum',
        'Gentium Basic, serif' => 'Gentium Basic',
        'Gravitas One, serif' => 'Gravitas One',
        'Istok Web, sans-serif' => 'Istok Web',
        'Italiana, serif' => 'Italiana',
        'Josefin Slab, sans-serif' => 'Josefin Slab',
        'Jura, sans-serif' => 'Jura',
        'Kreon, serif' => 'Kreon',
        'Lato, sans-serif' => 'Lato',
        'Ledger Regular, sans-serif' => 'Ledger Regular',
        'Lobster, cursive' => 'Lobster',
        'Montserrat, sans-serif' => 'Montserrat',
        'Nobile, sans-serif' => 'Nobile',
        'Old Standard TT, serif' => 'Old Standard TT',
        'Open Sans, sans-serif' => 'Open Sans',
        'Oswald, sans-serif' => 'Oswald',
        'Pacifico, cursive' => 'Pacifico',
        'Raleway, sans-serif' => 'Raleway',
        'Rokkitt, serif' => 'Rokkit',
        'Playfair Display, serif' => 'Playfair Display',
        'Poly, serif' => 'Poly',
        'PT Sans, sans-serif' => 'PT Sans',
        'PT Serif, serif' => 'PT Serif',
        'Quattrocento, serif' => 'Quattrocento',
        'Raleway, cursive' => 'Raleway',
        'Roboto, sans-serif' => 'Roboto',
        'Roboto Condensed, sans-serif' => 'Roboto Condensed',
        'Roboto Slab, serif' => 'Roboto Slab',
        'Signika, sans-serif' => 'Signika',
        'Stalemate, cursive' => 'Stalemate',
        'Source Sans Pro, sans-serif' => 'Source Sans Pro',
        'Ubuntu, sans-serif' => 'Ubuntu',
        'Vollkorn, serif' => 'Vollkorn',
        'Yanone Kaffeesatz, sans-serif' => 'Yanone Kaffeesatz'
    );
    return $google_faces;
}