<?php
/*
 * Waboot View 
 */
?>
<div class="title-wrapper">
    <?php if(\Waboot\functions\get_behavior('title-position') == 'top') : ?>
        <div class="container">
    <?php endif; ?>
    <?php \Waboot\template_tags\wrapped_title('<h1 class="page-title entry-title">','</h1>',$title); ?>
    <?php if(\Waboot\functions\get_behavior('title-position') == 'top') : ?>
        </div>
    <?php endif; ?>
</div>