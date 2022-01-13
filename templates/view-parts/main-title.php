<?php if( !is_front_page() ) : ?>
    <div class="main__title <?php echo $classes; ?>">
        <?php
        if ( function_exists('yoast_breadcrumb') ) {
            yoast_breadcrumb( '<nav class="breadcrumbs">','</nav>' );
            }
        ?>

        <?php do_action('waboot/layout/title/before'); ?>
        <?php \Waboot\inc\wrappedTitle('<h1>','</h1>',$title); ?>
        <?php do_action('waboot/layout/title/after'); ?>
    </div>
<?php endif; ?>
