<?php get_header(); ?>
    <div id="main-wrapper">
	    <?php
	    /*
		 * main_top zone
		 */
	    Waboot()->layout->render_zone("main-top");
	    ?>
	    <div class="<?php \Waboot\template_tags\container_classes(); ?>">
		    <div class="row">
			    <?php
			    /*
			     * content zone
			     */
			    try{
				    Waboot()->layout->render_zone("content");
			    }catch(Exception $e){
				    $e = new \WBF\components\mvc\HTMLView("templates/view-parts/content-errors.php");
				    $e->clean()->display(['Error' => $e,'message' => $e->getMessage()]);
			    }
			    ?>
			    <?php
			    /*
			     * sidebars
			     */
			    ?>
			    <?php get_sidebar(); ?>
		    </div><!-- .row -->
	    </div><!-- container -->
	    <?php
	    /*
		 * main_bottom zone
		 */
	    Waboot()->layout->render_zone("main-bottom");
	    ?>
    </div><!-- #main-wrapper -->
<?php get_footer(); ?>