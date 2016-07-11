<?php get_header(); ?>
	<?php
		$main_wrapper_vars = \Waboot\functions\get_main_wrapper_template_vars();
	?>
	<div id="main-wrapper" class="<?php echo $main_wrapper_vars['classes']; ?>">
		<div class="main-inner" class="<?php echo of_get_option( 'main_width', 'container' ); ?>">
			<?php
			/*
			 * main-top zone
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
			</div><!-- site-main -->
			<?php
			/*
			 * main-bottom zone
			 */
			Waboot()->layout->render_zone("main-bottom");
			?>
		</div><!-- .main-inner -->
    </div><!-- #main-wrapper -->
<?php get_footer(); ?>