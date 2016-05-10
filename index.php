<?php get_header(); ?>
    <div id="main-wrapper">
	    <div class="<?php \Waboot\template_tags\container_classes(); ?>">
		    <div class="row">
			    <?php if(function_exists("Waboot")): ?>
				    <?php
				    /*
				     * main zone
				     */
				    try{
					    Waboot()->layout->render_zone("main");
				    }catch(Exception $e){
					    (new \WBF\includes\mvc\HTMLView("templates/view-parts/main-errors.php"))->clean()->display([
						    'Error' => $e,
						    'message' => $e->getMessage()
					    ]);
				    }
				    ?>
				    <?php
				    /*
				     * sidebars
				     */
				    ?>
				    <?php get_sidebar(); ?>
			    <?php endif; ?>
		    </div><!-- .row -->
	    </div><!-- container -->
    </div><!-- #main-wrapper -->
<?php get_footer(); ?>