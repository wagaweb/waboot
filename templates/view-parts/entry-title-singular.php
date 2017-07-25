<?php
/*
 * Waboot View 
 */
?>
<div class="title-wrapper">
    <?php if(\Waboot\functions\get_behavior('title-position') === 'top') : ?>
        <?php do_action('waboot/layout/singular/page_title/wrapper/before'); ?>
        <div class="container">
	        <?php do_action('waboot/layout/singular/page_title/before'); ?>
	        <?php \Waboot\template_tags\wrapped_title('<h1 class="page-title entry-title">','</h1>',$title); ?>
	        <?php do_action('waboot/layout/singular/page_title/after'); ?>
        </div>
	    <?php do_action('waboot/layout/singular/page_title/wrapper/after'); ?>
    <?php else: ?>
        <?php do_action('waboot/layout/singular/page_title/before'); ?>
        <?php \Waboot\template_tags\wrapped_title('<h1 class="page-title entry-title">','</h1>',$title); ?>
        <?php do_action('waboot/layout/singular/page_title/after'); ?>
    <?php endif; ?>
</div>