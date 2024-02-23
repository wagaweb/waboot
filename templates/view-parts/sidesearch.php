<div class="slidein sidesearch" data-slidein-search data-slidein-toggle="#slidein-search__toggle">

    <form id="searchform" class="search__form" role="search" action="<?php echo site_url(); ?>" method="get">
        <input id="s" name="s" type="text" placeholder="<?php esc_attr_e( 'Search &hellip;', LANG_TEXTDOMAIN ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>">
        <button class="btn" id="searchsubmit" type="submit" name="submit"><?php _e( 'Search', LANG_TEXTDOMAIN ); ?></button>
    </form>

    <button data-slidein-close aria-label="<?php _e('Close Slide', LANG_TEXTDOMAIN); ?>">
        <i class="fa-light fa-xmark"></i>
    </button>
</div>