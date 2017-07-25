<?php
/*
 * Waboot View 
 */
?>
<div class="title-wrapper">
    <?php do_action('waboot/layout/singular/page_title/before'); ?>
    <?php \Waboot\template_tags\wrapped_title('<h1 class="page-title entry-title">','</h1>',$title); ?>
    <?php do_action('waboot/layout/singular/page_title/after'); ?>
</div>