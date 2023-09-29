<?php if ( have_posts() ) : ?>
    <div class="article__grid">
        <?php
        // Start the Loop
        while ( have_posts() ) : the_post();
            get_template_part( '/templates/parts/content', 'search' );
        endwhile;
        ?>
        <?php \Waboot\inc\renderPostNavigation( 'nav-below' ); ?>
    </div>
<?php else : ?>
    <?php
    // No results
    get_template_part( '/templates/parts/content', 'none' );
    ?>
<?php endif; ?>
