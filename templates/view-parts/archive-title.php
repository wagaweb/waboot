<header class="title__wrapper">
    <?php do_action('waboot/layout/archive/page_title/before'); ?>
    <?php \Waboot\template_tags\wrapped_title('<h1 class="entry__title archive__title">','</h1>',$title); ?>
    <?php do_action('waboot/layout/archive/page_title/after'); ?>
</header>