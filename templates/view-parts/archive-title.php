<?php
/*
 * Waboot View 
 */
?>
<div class="title-wrapper">
    <?php do_action('waboot/layout/archive/page_title/before'); ?>
    <?php \Waboot\template_tags\wrapped_title('<h1 class="page-title archive-title">','</h1>',$title); ?>
    <?php do_action('waboot/layout/archive/page_title/after'); ?>
</div>