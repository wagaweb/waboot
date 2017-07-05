<div class="navbar-header">
	<?php if($offcanvas): ?>
		<button type="button" class="navbar-toggle" data-toggle="offcanvas" data-target=".navbar-mobile-collapse" data-canvas="body">
			<span class="sr-only"><?php _e("Toggle navigation","waboot"); ?></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</button>	
	<?php else : ?>
		<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-main-collapse">
			<span class="sr-only"><?php _e("Toggle navigation","waboot"); ?></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</button>	
	<?php endif; ?>

    <a class="navbar-brand" href="<?php echo home_url( '/' ); ?>">
        <?php if ( \Waboot\functions\get_option('mobile_logo') != "" ) {
            \Waboot\template_tags\mobile_logo();
        }elseif( \Waboot\functions\get_option('desktop_logo') != ""){
            \Waboot\template_tags\desktop_logo();
        }else{
            \Waboot\template_tags\site_title();
        }?>
    </a>
</div>

<div class="collapse navbar-collapse navbar-main-collapse">
	<?php wp_nav_menu([
		'theme_location' => 'main',
		'depth' => 0,
		'container' => false,
        'menu_class' => apply_filters('waboot/navigation/main/class', 'nav navbar-nav'),
        'walker' => new \WBF\components\navwalker\Bootstrap_NavWalker(),
		'fallback_cb' => '\WBF\components\navwalker\Bootstrap_NavWalker::fallback'
	]); 
	?>
    <?php if($display_searchbar): ?>
        <form id="searchform" class="navbar-form navbar-right" role="search" action="<?php echo site_url(); ?>" method="get">
            <div class="form-group">
                <input id="s" name="s" type="text" class="form-control" placeholder="<?php esc_attr_e( 'Search &hellip;', 'waboot' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>">
            </div>
            <button id="searchsubmit" type="submit" name="submit" class="btn btn-default">Submit</button>
        </form>
    <?php endif; ?>
</div>

<?php if($offcanvas) { echo $navbar_offcanvas; } ?>