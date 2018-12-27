<?php $vars = \Waboot\functions\get_archives_template_vars(); ?>

<?php if($vars['display_page_title']) : ?>
    <?php \Waboot\template_tags\archive_page_title(); ?>
<?php endif; ?>
<?php if(!empty($vars['tpl'])) : ?>
    <?php get_template_part($vars['tpl']); ?>
<?php else: ?>
    <?php get_template_part('templates/archive/archive'); ?>
<?php endif; ?>