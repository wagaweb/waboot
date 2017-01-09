<?php $vars = \Waboot\functions\get_archives_template_vars(); ?>
<section id="main-wrap" class="<?php echo apply_filters( 'waboot_mainwrap_container_class', 'content-area col-sm-8' ); ?>">
	<main id="main" class="site-main" role="main">
		<?php if (of_get_option('waboot_blogpage_title_position') == "bottom" && of_get_option('waboot_blogpage_displaytitle') == "1") : ?>
			<header class="page-header">
				<?php if($vars['display_title']): ?>
					<?php \Waboot\template_tags\wrapped_title('<h1 class="page-title entry-title">','</h1>',$vars['page_title']); ?>
				<?php endif; ?>
				<?php
				$author_description = get_the_author_meta("description",$post->post_author);
				if ( ! empty( $author_description ) )
					printf( '<div class="author-description">%s</div>', $author_description );
				?>
			</header>
		<?php endif; ?>
		<?php if(have_posts()){ ?>
			<?php if($vars['display_nav_above']) \Waboot\template_tags\post_navigation('nav-above'); ?>
			<div class="<?php echo $vars['blog_class']; ?>">
				<?php
				while(have_posts()){
					the_post();
					\Waboot\functions\get_template_part( '/templates/wordpress/parts/content', get_post_format() );
				}
				?>
			</div>
			<?php if($vars['display_nav_below']) \Waboot\template_tags\post_navigation('nav-below'); ?>
			<?php
		}else{
			// No results
			get_template_part( '/templates/wordpress/parts/content', 'none' );
		} //have_posts ?>
	</main><!-- #main -->
</section><!-- #main-wrap -->