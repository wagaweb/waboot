<?php get_header(); ?>
    <div id="main-wrapper" class="<?php \Waboot\template_tags\main_wrapper_classes(); ?>">
	    <?php if(function_exists("Waboot")): ?>
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
		    <?php get_sidebar(); ?>
		<?php endif; ?>
    </div><!-- #main-wrapper -->
<?php get_footer(); ?>