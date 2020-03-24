<div class="error-404 not-found">

    <h1>
        <span class="title404">404</span><br/>
        <?php _e( 'Oops! That page can&rsquo;t be found.', LANG_TEXTDOMAIN ); ?>
    </h1>

    <p><?php _e( 'It looks like nothing was found at this location. Maybe try a search or one of the links below?', LANG_TEXTDOMAIN ); ?></p>
    <?php get_search_form(); ?>
    <p><?php _e( 'Let\'s return to the', LANG_TEXTDOMAIN ); ?> <a href="<?php echo get_site_url(); ?>">Homepage</a> </p>

</div>
