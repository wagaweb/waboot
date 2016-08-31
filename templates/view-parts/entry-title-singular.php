<?php
/*
 * Waboot View 
 */
?>
<div class="title-wrapper">
    <?php if(\Waboot\functions\get_behavior('title-position','bottom') == 'top') : ?>
        <div class="container">
    <?php endif; ?>
        <?php \Waboot\template_tags\wrapped_title('<h1 class="page-title">','</h1>',$title); ?>
    <?php if(\Waboot\functions\get_behavior('title-position','bottom') == 'top') : ?>
        </div>
    <?php endif; ?>
</div>