<?php get_header(); ?>
    <div id="main-wrapper">
	    <?php if(function_exists("Waboot")): ?>
	        <?php Waboot()->layout->render_zone("aside-primary"); ?>
	        <?php
			    try{
				    Waboot()->layout->render_zone("main");
			    }catch(Exception $e){
				    (new \WBF\includes\mvc\HTMLView("templates/view-parts/main-errors.php"))->clean()->display([
					    'Error' => $e,
					    'message' => $e->getMessage()
				    ]);
			    }
			 ?>
	        <?php Waboot()->layout->render_zone("aside-secondary"); ?>
		<?php endif; ?>
    </div><!-- #main-wrapper -->
<?php get_footer(); ?>