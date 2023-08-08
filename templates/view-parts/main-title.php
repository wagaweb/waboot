<div class="main__title <?php echo $classes; ?>">
    <?php do_action('waboot/layout/title/before'); ?>
    <?php \Waboot\inc\wrappedTitle('<h1>','</h1>',$title); ?>
    <?php do_action('waboot/layout/title/after'); ?>
</div>
