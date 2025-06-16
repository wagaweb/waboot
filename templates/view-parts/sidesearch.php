<div class="slidein sidesearch" data-slidein-search data-slidein-toggle="#slidein-search__toggle" aria-hidden="true" inert>
    <form id="searchform" class="search__form" action="<?php echo esc_url(site_url('/')); ?>" method="get" role="search" aria-label="<?php esc_attr_e('Search form', LANG_TEXTDOMAIN); ?>">
        <label for="s" class="sr-only"><?php _e('Search for:', LANG_TEXTDOMAIN); ?></label>
        <input
                id="s"
                name="s"
                type="search"
                placeholder="<?php esc_attr_e( 'Search &hellip;', LANG_TEXTDOMAIN ); ?>"
                value="<?php echo esc_attr( get_search_query() ); ?>"
                aria-label="<?php esc_attr_e('Search query', LANG_TEXTDOMAIN); ?>"
                autocomplete="off" tabindex="-1"
        >
        <button class="btn" id="searchsubmit" type="submit" name="submit" tabindex="-1">
            <?php _e( 'Search', LANG_TEXTDOMAIN ); ?>
        </button>
    </form>

    <button data-slidein-close aria-label="Close" tabindex="-1"><i class="fal fa-times"></i></button>
</div>
