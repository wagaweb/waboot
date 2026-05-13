<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <?php \Waboot\inc\site_head(); ?>
</head>

<body <?php body_class(); ?>>

    <?php do_action('waboot/layout/page-before'); ?>

    <header class="header">

        <a class="skip-link sr-only sr-only-focusable" href="#main">
            <?php _e('Skip to content', LANG_TEXTDOMAIN); ?>
        </a>

        <?php do_action('waboot/layout/header'); ?>

    </header>