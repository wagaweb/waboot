<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<?php do_action("waboot/head/start"); ?>
	<title><?php wp_title( ' | ', true, 'right' ); ?></title>
	<?php wp_head(); ?>
	<?php do_action("waboot/head/end"); ?>
</head>