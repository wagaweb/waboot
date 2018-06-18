<header class="entry__header main__title<?php if(isset($title_position)): ?> main__title--<?php echo $title_position; ?><?php endif; ?>">
    <?php do_action('waboot/layout/singular/page_title/before'); ?>
    <?php \Waboot\template_tags\wrapped_title('<h1 class="entry__title">','</h1>',$title); ?>
    <?php do_action('waboot/layout/singular/page_title/after'); ?>
</header>