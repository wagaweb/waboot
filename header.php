<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<?php \Waboot\template_tags\site_head(); ?>
</head>
<body <?php body_class(); ?> >
    <?php \Waboot\template_tags\render_zone("page-before"); ?>
	<div id="site-page" class="site-page hfeed site">
		<div id="site-page__wrapper" class="site-page__wrapper <?php echo WabootLayout()->get_container_grid_class(\Waboot\functions\get_option( 'page_width', WabootLayout()->get_grid_class(\Waboot\Layout::GRID_CLASS_CONTAINER) ) ); ?>">
        <?php do_action('waboot/site-page/start'); ?>
		<!-- BEGIN: site-header -->
		<div class="site-header" data-zone="header">
            <?php \Waboot\template_tags\render_zone("header"); ?>
            <?php do_action("waboot/header"); ?>
		</div>
		<!-- END: site-header -->
		
