<?php if ( have_posts() ) : ?>
    <?php
    // Start the Loop
    while ( have_posts() ) : the_post();
        get_template_part( '/templates/parts/content', 'search' );
    endwhile;
    ?>
    <?php \Waboot\inc\renderPostNavigation( 'nav-below' ); ?>
<?php else : ?>
    <?php
    // No results
    get_template_part( '/templates/parts/content', 'none' );
    ?>
<?php endif; ?>
