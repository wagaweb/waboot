<div
        class="slidein sidesearch"
        data-slidein-search
        data-slidein-toggle="#slidein-search__toggle"
        id="slidein-search"
        role="search"
        aria-label="<?php esc_attr_e('Site search', LANG_TEXTDOMAIN); ?>"
        aria-hidden="true"
>
    <form id="searchform" class="search__form" action="<?php echo esc_url(site_url('/')); ?>" method="get" role="search" aria-label="<?php esc_attr_e('Search form', LANG_TEXTDOMAIN); ?>">
        <label for="s" class="sr-only"><?php _e('Search for:', LANG_TEXTDOMAIN); ?></label>
        <input
                id="s"
                name="s"
                type="search"
                placeholder="<?php esc_attr_e( 'Search &hellip;', LANG_TEXTDOMAIN ); ?>"
                value="<?php echo esc_attr( get_search_query() ); ?>"
                aria-label="<?php esc_attr_e('Search query', LANG_TEXTDOMAIN); ?>"
                autocomplete="off"
        >
        <button class="btn" id="searchsubmit" type="submit" name="submit">
            <?php _e( 'Search', LANG_TEXTDOMAIN ); ?>
        </button>
    </form>

    <button
            type="button"
            class="slidein-close-btn"
            data-slidein-close
            aria-label="<?php esc_attr_e('Close search', LANG_TEXTDOMAIN); ?>"
    >
        <i class="fal fa-times" aria-hidden="true"></i>
    </button>
</div>
