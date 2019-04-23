<header class="entry__header archive__header<?php if(isset($title_position)): ?> main__title--<?php echo $title_position; ?><?php endif; ?>">
    <?php do_action('waboot/layout/archive/page_title/before'); ?>
    <?php \Waboot\template_tags\wrapped_title('<h1 class="entry__title">','</h1>',$title); ?>
    <?php do_action('waboot/layout/archive/page_title/after'); ?>
</header>