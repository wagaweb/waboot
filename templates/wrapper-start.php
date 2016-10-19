<?php
	$main_wrap_classes = apply_filters( 'waboot_mainwrap_container_class', 'content-area col-sm-8' );
	if(is_404() || is_attachment()){
		$main_wrap_classes = "content-area col-sm-12";
	}
?>

<div id="main-wrap" class="<?php echo $main_wrap_classes; ?>">
	<main id="main" class="site-main" role="main">