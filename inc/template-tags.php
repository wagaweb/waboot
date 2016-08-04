<?php

if(!function_exists('waboot_site_title')):
	/**
	 * Displays site title
	 * @since 0.13.4
	 */
	function waboot_site_title() {
		$element = apply_filters("waboot/site_title/tag",'h1');
		$display_name = call_user_func(function(){
			$custom_name = of_get_option("custom_site_title","");
			if($custom_name && !empty($custom_name)){
				return $custom_name;
			}else{
				return get_bloginfo("name");
			}
		});
		$link = sprintf( '<a href="%s" title="%s" class="navbar-brand" rel="home">%s</a>', trailingslashit( home_url() ), esc_attr( get_bloginfo( 'name' ) ), $display_name );
		$output = '<' . $element . ' id="site-title" class="site-title">' . $link . '</' . $element .'>';
		echo apply_filters( 'waboot/site_title/markup', $output );
	}
endif;

if(!function_exists('waboot_site_description')):
	/**
	 * Displays site description
	 * @since 0.13.4
	 */
	function waboot_site_description() {
		if(!of_get_option("show_site_description",0)) return;
		// Use H2
		$element = 'h2';
		// Put it all together
		$description = '<' . $element . ' id="site-description" class="site-description">' . esc_html( get_bloginfo( 'description' ) ) . '</' . $element . '>';
		// Echo the description
		echo apply_filters( 'waboot/site_description/markup', $description );
	}
endif;

if(!function_exists("waboot_mobile_logo")) :
	/**
	 * Prints the mobile logo
	 */
	function waboot_mobile_logo($contest = "header", $linked = false){
		if($linked){
			$tpl = "<a href='%s'><img src='%s' class='img-responsive' /></a>";
			printf($tpl,home_url( '/' ),waboot_get_mobile_logo($contest));
		}else{
			$tpl = "<img src='%s' class='img-responsive' />";
			printf($tpl,waboot_get_mobile_logo($contest));
		}
	}
endif;

if(!function_exists("waboot_get_mobile_logo")) :
	/**
	 * Get the mobile logo, or an empty string.
	 * @return string
	 */
	function waboot_get_mobile_logo($contest = "header"){
		switch($contest){
			case "offcanvas":
				$mobile_logo = of_get_option( 'mobile_offcanvas_logo', "");
				break;
			default:
				$mobile_logo = of_get_option( 'mobile_logo', "");
				break;
		}
		return $mobile_logo;
	}
endif;

if(!function_exists("waboot_desktop_logo")) :
	/**
	 * Prints the desktop logo
	 */
	function waboot_desktop_logo($linked = false){
		if($linked){
			$tpl = "<a href='%s'><img src='%s' class='waboot-desktop-logo' /></a>";
			printf($tpl,home_url( '/' ),waboot_get_desktop_logo());
		}else{
			$tpl = "<img src='%s' class='waboot-desktop-logo' />";
			printf($tpl,waboot_get_desktop_logo());
		}
	}
endif;

if(!function_exists("waboot_get_desktop_logo")) :
	/**
	 * Get the desktop logo, or an empty string
	 * @return string
	 */
	function waboot_get_desktop_logo(){
		$desktop_logo = of_get_option( 'waboot_logo_in_navbar', "");
		return $desktop_logo;
	}
endif;

if(!function_exists('waboot_content_nav' )):
	/**
	 * Display navigation to next/previous pages when applicable
	 * 
	 * @param string $nav_id
	 * @param bool $show_pagination
	 * @param WP_Query|bool $query
	 * @param int|bool $current_page
	 * @param string $paged_var_name You can supply different paged var name for multiple pagination. The name must be previously registered with add_rewrite_tag()
	 * 
	 * @from Waboot
	 */
	function waboot_content_nav( $nav_id, $show_pagination = false, $query = false, $current_page = false, $paged_var_name = "paged" ) {
		// Return early if theme options are set to hide nav
		if ( 'nav-below' == $nav_id && ! of_get_option( 'waboot_content_nav_below', 1 ) || 'nav-above' == $nav_id && ! of_get_option( 'waboot_content_nav_above' ) ) return;

		if(!$query){
			global $wp_query;
			$query = $wp_query;
		}else{
			if(!$query instanceof WP_Query){
				return; // Return early if query is invalid
			}
		}

		$nav_class = 'site-navigation paging-navigation';
		if(is_single()){
			$nav_class .= ' post-navigation';
		}else{
			$nav_class .= ' paging-navigation';
		}
		?>
		<nav id="<?php echo $nav_id; ?>" class="<?php echo $nav_class; ?>">
			<ul class="pagination">
				<?php
				/*
				 * Navigation links for single posts
				 */
				if(is_single()) :
					previous_post_link( '<li class="previous">%link</li>', '<span class="meta-nav">' . _x( '&laquo;', 'Previous post link', 'waboot' ) . '</span> %title' );
					next_post_link( '<li class="next">%link</li>', '%title <span class="meta-nav">' . _x( '&raquo;', 'Next post link', 'waboot' ) . '</span>' );
				elseif($query->max_num_pages > 1 && (is_home() || is_archive() || is_search() || is_singular())) : // navigation links for home, archive, search pages and common pages
					/*
					 * Navigation links for home, archive, and search pages
					 */
					if($show_pagination){
						$big = 999999999; // need an unlikely integer
						if($paged_var_name != "paged"){
							$base =  add_query_arg([
								$paged_var_name => "%#%"
							]);
							$base = home_url().$base;
						}else{
							$base =  str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) );
						}
						$paginate = paginate_links(array(
							'base' => $base,
							'format' => '?'.$paged_var_name.'=%#%',
							'current' => $current_page ? intval($current_page) : max( 1, intval(get_query_var($paged_var_name)) ),
							'total' => $query->max_num_pages
						));

						$paginate_array = explode("\n",$paginate);
						foreach($paginate_array as $k => $link){
							$paginate_array[$k] = "<li>".$link."</li>";
						}

						$paginate = implode("\n",$paginate_array);

						echo $paginate;
					}else{
						if(get_next_posts_link()) : ?>
							<li class="pull-right"><?php next_posts_link( __( 'Next page <span class="meta-nav">&raquo;</span>', 'waboot' ), $query->max_num_pages ); ?></li>
						<?php endif;

						if(get_previous_posts_link()) : ?>
							<li class="pull-left"><?php previous_posts_link( __( '<span class="meta-nav">&laquo;</span> Previous page', 'waboot' ), $query->max_num_pages ); ?></li>
						<?php endif;
					}
				endif;
				?>
			</ul>
		</nav><!-- #<?php echo $nav_id; ?> -->
	<?php
	}
endif; // waboot_content_nav

if(!function_exists("waboot_entry_title")):
	/**
	 * Retrieve entry title; set it to H1 if in single view, otherwise set it to H2
	 * @since 0.1.0
	 * @param null $post
	 * @return mixed|void
	 */
	function waboot_entry_title($post = null) {
		if (!isset($post)) {
			global $post;
		}

		if(!is_archive()){
			if (get_behavior('show-title', $post->ID) == "0") return "";
        }

		$title = get_the_title($post->ID);

		if (mb_strlen($title) == 0)
			return "";

		$title = apply_filters("waboot_entry_title_text",$title); //@ waboot_entry_title_simple was deprecated

		if(is_singular() ) {
			$str = sprintf(apply_filters('waboot_entry_title_html_singular', '<h1 class="entry-title" itemprop="name">%s</h1>'), $title); //@waboot_entry_title_text_singular was deprecated
		}else{
			$str = sprintf(apply_filters('waboot_entry_title_html_posts', '<h2 class="entry-title" itemprop="name"><a class="entry-title" title="%s" rel="bookmark" href="%s">%s</a></h2>'), the_title_attribute('echo=0'), get_permalink(), $title); //@waboot_entry_title_text_posts was deprecated
		}

		return $str;
	}
endif;

if(!function_exists("waboot_index_title")):
	function waboot_index_title($prefix = "", $suffix = "", $display = true) {
		if (of_get_option('waboot_blogpage_displaytitle') == "1") {
			$title = $prefix . apply_filters('waboot_index_title_text', waboot_get_index_page_title()) . $suffix;
		} else {
			$title = "";
		}

		if ($display) {
			echo $title;
		}
		return $title;
	}
endif;

if(!function_exists("waboot_wc_shop_title")):
	function waboot_wc_shop_title($prefix = "", $suffix = "", $display = true) {
		if (of_get_option('waboot_blogpage_displaytitle') == "1") {
			$title = $prefix . apply_filters('waboot_index_title_text', waboot_get_wc_shop_page_title()) . $suffix;
		} else {
			$title = "";
		}

		if ($display) {
			echo $title;
		}
		return $title;
	}
endif;

if(!function_exists("waboot_archive_page_title")):
	/**
	 * Format archives page title
	 * @param string $prefix
	 * @param string $suffix
	 * @param bool|true $display
	 *
	 * @return string|void
	 */
	function waboot_archive_page_title($prefix = "", $suffix = "", $display = true){
		if (of_get_option('waboot_blogpage_displaytitle') == "1") {
			$output = waboot_get_archive_page_title();
			$output = $prefix.$output.$suffix;
		}else{
			$output = "";
		}
		if($display){
			echo $output;
		}
		return $output;
	}
endif;

if(!function_exists("waboot_wc_archive_page_title")):
	/**
	 * Format WC archives page title
	 * @param string $prefix
	 * @param string $suffix
	 * @param bool|true $display
	 *
	 * @return string|void
	 */
	function waboot_wc_archive_page_title($prefix = "", $suffix = "", $display = true){
		if (of_get_option('waboot_woocommerce_displaytitle') == "1") {
			$output = waboot_get_archive_page_title();
			$output = $prefix.$output.$suffix;
		}else{
			$output = "";
		}
		if($display){
			echo $output;
		}
		return $output;
	}
endif;

if(!function_exists('waboot_archive_sticky_posts')):
    /**
     * Display sticky posts on archive pages
     * @since 0.1.0
     * @param string $blog_style
     */
	function waboot_archive_sticky_posts($blog_style = "classic") {

        $sticky = get_option( 'sticky_posts' );
        if ( ! empty( $sticky ) ) {
            global $do_not_duplicate, $page, $paged;
            $do_not_duplicate = array();

            if ( is_category() ) {
                $cat_ID = get_query_var( 'cat' );
                $sticky_args = array(
                    'post__in'    => $sticky,
                    'cat'         => $cat_ID,
                    'post_status' => 'publish',
                    'paged'       => $paged
                );

            } elseif ( is_tag() ) {
                $current_tag = get_queried_object_id();
                $sticky_args = array(
                    'post__in'     => $sticky,
                    'tag_id'       => $current_tag,
                    'post_status'  => 'publish',
                    'paged'        => $paged
                );
            }

            if ( ! empty( $sticky_args ) ):
                $sticky_posts = new WP_Query( $sticky_args );

                if ( $sticky_posts->have_posts() ):
                    global $post;

                    while ( $sticky_posts->have_posts() ) : $sticky_posts->the_post();
                        array_push( $do_not_duplicate, $post->ID );
	                    if($blog_style != "classic"){
		                    get_template_part( '/templates/parts/content', "blog-".$blog_style );
	                    }else{
		                    get_template_part( '/templates/parts/content', get_post_format() );
	                    }
                    endwhile;
                endif; // if have posts
            endif; // if ( ! empty( $sticky_args ) )
        } //if not empty sticky
    }
endif; //waboot_archive_sticky_posts

if(!function_exists('waboot_comment')) :
    /**
     * Template for comments and pingbacks.
     *
     * Used as a callback by wp_list_comments() for displaying the comments.
     */
    function waboot_comment( $comment, $args, $depth ) {
        $GLOBALS['comment'] = $comment;

        if ( 'pingback' == $comment->comment_type || 'trackback' == $comment->comment_type ) : ?>

            <li id="comment-<?php comment_ID(); ?>" <?php comment_class(); ?>>
            <div class="comment-body">
                <?php _e( 'Pingback:', 'waboot' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( __( 'Edit', 'waboot' ), '<span class="edit-link">', '</span>' ); ?>
            </div>

        <?php else : ?>

        <li id="comment-<?php comment_ID(); ?>" <?php comment_class( empty( $args['has_children'] ) ? '' : 'parent' ); ?>>
            <article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
                <footer class="comment-meta">
                    <div class="comment-author vcard">
                        <?php if ( 0 != $args['avatar_size'] ) echo get_avatar( $comment, $args['avatar_size'] ); ?>
                        <?php printf( __( '%s <span class="says">says:</span>', 'waboot' ), sprintf( '<cite class="fn">%s</cite>', get_comment_author_link() ) ); ?>
                    </div><!-- .comment-author -->

                    <div class="comment-metadata">
                        <a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
                            <time datetime="<?php comment_time( 'c' ); ?>">
                                <?php printf( _x( '%1$s at %2$s', '1: date, 2: time', 'waboot' ), get_comment_date(), get_comment_time() ); ?>
                            </time>
                        </a>
                        <?php edit_comment_link( __( 'Edit', 'waboot' ), '<span class="edit-link">', '</span>' ); ?>
                    </div><!-- .comment-metadata -->

                    <?php if ( '0' == $comment->comment_approved ) : ?>
                        <p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'waboot' ); ?></p>
                    <?php endif; ?>
                </footer><!-- .comment-meta -->

                <div class="comment-content">
                    <?php comment_text(); ?>
                </div><!-- .comment-content -->

                <?php
                comment_reply_link( array_merge( $args, array(
                    'add_below' => 'div-comment',
                    'depth'     => $depth,
                    'max_depth' => $args['max_depth'],
                    'before'    => '<div class="reply">',
                    'after'     => '</div>',
                ) ) );
                ?>
            </article><!-- .comment-body -->

        <?php
        endif;
    }
endif; // ends check for waboot_comment()

if(!function_exists("waboot_do_sidebar")):
	/**
	 * Determines the theme layout and active sidebars, and prints the HTML structure
	 * with appropriate grid classes depending on which are activated.
	 *
	 * @since 0.1.0
	 * @uses waboot_sidebar_class()
	 * @param string $prefix Prefix of the widget to be displayed. Example: "footer" for footer-1, footer-2, etc.
	 */
	function waboot_do_sidebar( $prefix = false ) {
	    if ( ! $prefix )
	        _doing_it_wrong( __FUNCTION__, __( 'You must specify a prefix when using waboot_do_sidebar.', 'waboot' ), '1.0' );

	        // Get our grid class
	        $sidebar_class = waboot_sidebar_class( $prefix );

	        if ( $sidebar_class ): ?>

	            <div class="<?php echo $prefix; ?>-sidebar-row row">
	                <?php do_action( 'waboot_sidebar_row_top' );

	                if ( is_active_sidebar( $prefix.'-1' ) ): ?>
	                    <aside id="<?php echo $prefix; ?>-sidebar-1" class="sidebar widget<?php echo $sidebar_class; ?>">
	                        <?php dynamic_sidebar( $prefix.'-1' ); ?>
	                    </aside>
	                <?php endif;


	                if ( is_active_sidebar( $prefix.'-2' ) ): ?>
	                    <aside id="<?php echo $prefix; ?>-sidebar-2" class="sidebar widget<?php echo $sidebar_class; ?>">
	                        <?php dynamic_sidebar( $prefix.'-2' ); ?>
	                    </aside>
	                <?php endif;


	                if ( is_active_sidebar( $prefix.'-3' ) ): ?>
	                    <aside id="<?php echo $prefix; ?>-sidebar-3" class="sidebar widget<?php echo $sidebar_class; ?>">
	                        <?php dynamic_sidebar( $prefix.'-3' ); ?>
	                    </aside>
	                <?php endif;


	                if ( is_active_sidebar( $prefix.'-4' ) ): ?>
	                    <aside id="<?php echo $prefix; ?>-sidebar-4" class="sidebar widget<?php echo $sidebar_class; ?>">
	                        <?php dynamic_sidebar( $prefix.'-4' ); ?>
	                    </aside>
	                <?php endif;

	                do_action( 'waboot_sidebar_row_bottom' ); ?>
	            </div><!-- .row -->

	        <?php endif; //$sidebar_class
	}
endif;

if(!function_exists("waboot_sidebar_class")):
	/**
	 * Count the number of active widgets to determine dynamic wrapper class
	 * @since 0.1.0
	 */
	function waboot_sidebar_class( $prefix = false ) {
	    if ( ! $prefix )
	        _doing_it_wrong( __FUNCTION__, __( 'You must specify a prefix when using waboot_sidebar_class.', 'waboot' ), '1.0' );

	    $count = 0;

	    if ( is_active_sidebar( $prefix.'-1' ) )
	        $count++;

	    if ( is_active_sidebar( $prefix.'-2' ) )
	        $count++;

	    if ( is_active_sidebar( $prefix.'-3' ) )
	        $count++;

	    if ( is_active_sidebar( $prefix.'-4' ) )
	        $count++;

	    $class = '';

	    switch ( $count ) {
	        case '1':
	            $class = ' col-sm-12';
	            break;

	        case '2':
	            $class = ' col-sm-6';
	            break;

	        case '3':
	            $class = ' col-sm-4';
	            break;

	        case '4':
	            $class = ' col-sm-3';
	            break;
	    }

	    if ( $class )
	        return $class;
	}
endif;

if(!function_exists('waboot_the_attached_image')) :
    /**
     * Prints the attached image with a link to the next attached image.
     */
    function waboot_the_attached_image() {

        $post                = get_post();
        $attachment_size     = apply_filters( 'waboot_attachment_size', array( 1200, 1200 ) );
        $next_attachment_url = wp_get_attachment_url();

        /**
         * Grab the IDs of all the image attachments in a gallery so we can get the
         * URL of the next adjacent image in a gallery, or the first image (if
         * we're looking at the last image in a gallery), or, in a gallery of one,
         * just the link to that image file.
         */
        $attachment_ids = get_posts( array(
            'post_parent'    => $post->post_parent,
            'fields'         => 'ids',
            'numberposts'    => -1,
            'post_status'    => 'inherit',
            'post_type'      => 'attachment',
            'post_mime_type' => 'image',
            'order'          => 'ASC',
            'orderby'        => 'menu_order ID'
        ) );

        // If there is more than 1 attachment in a gallery...
        if ( count( $attachment_ids ) > 1 ) {
            foreach ( $attachment_ids as $attachment_id ) {
                if ( $attachment_id == $post->ID ) {
                    $next_id = current( $attachment_ids );
                    break;
                }
            }

            // get the URL of the next image attachment...
            if ( $next_id )
                $next_attachment_url = get_attachment_link( $next_id );

            // or get the URL of the first image attachment.
            else
                $next_attachment_url = get_attachment_link( array_shift( $attachment_ids ) );
        }

        printf( '<a href="%1$s" title="%2$s" rel="attachment">%3$s</a>',
            esc_url( $next_attachment_url ),
            the_title_attribute( array( 'echo' => false ) ),
            wp_get_attachment_image( $post->ID, $attachment_size )
        );
    }
endif;

if(!function_exists('waboot_archive_get_posts')):
    /**
     * Display archive posts and exclude sticky posts
     * @since 0.1.0
     * @unused
     */
    function waboot_archive_get_posts() {

        global $do_not_duplicate, $page, $paged;

        if ( is_category() ) {
            $cat_ID = get_query_var( 'cat' );
            $args = array(
                'cat'                 => $cat_ID,
                'post_status'         => 'publish',
                'post__not_in'        => array_merge( $do_not_duplicate, get_option( 'sticky_posts' ) ),
                'ignore_sticky_posts' => 1,
                'paged'               => $paged
            );
            $wp_query = new WP_Query( $args );

        } elseif (is_tag() ) {
            $current_tag = single_tag_title( "", false );
            $args = array(
                'tag_slug__in'        => array( $current_tag ),
                'post_status'         => 'publish',
                'post__not_in'        => array_merge( $do_not_duplicate, get_option( 'sticky_posts' ) ),
                'ignore_sticky_posts' => 1,
                'paged'               => $paged
            );
            $wp_query = new WP_Query( $args );

        } else {
            new WP_Query();
        }
    }
endif;

if(!function_exists("waboot_breadcrumb")):
    /**
     * Display the breadcrumb for $post_id or global $post->ID
     * @param null $post_id
     * @param string $current_location the current location of breadcrumb. Not used at the moment.
     * @param array $args settings for breadcrumb (see: waboot_breadcrumb_trail() documentation)
     * @since 0.3.10
     */
    function waboot_breadcrumb($post_id = null, $current_location = "", $args = array()) {
        global $post;

	    //Get post ID
	    if(!isset($post_id)){
		    if(isset($post) && isset($post->ID) && $post->ID != 0){
			    $post_id = $post->ID;
		    }
	    }

        if (function_exists('waboot_breadcrumb_trail')) {
            if(is_404()) return;

	        $current_page_type = wbft_current_page_type();

	        $args = wp_parse_args($args, array(
		        'container' => "div",
		        'separator' => "/",
		        'show_browse' => false,
		        'additional_classes' => ""
	        ));

	        $allowed_locations = call_user_func(function(){
		        $bc_locations = of_get_option('waboot_breadcrumb_locations',[]);
		        $allowed = array();
		        foreach($bc_locations as $k => $v){
			        if($v == "1"){
				        $allowed[] = $k;
			        }
		        }
		        return $allowed;
	        });

	        if($current_page_type != "common"){
		        //We are in some sort of homepage
		        if(in_array("homepage", $allowed_locations)) {
			        waboot_breadcrumb_trail($args);
		        }
		        /*switch($current_page_type){
			        case "default_home":
				        break;
			        case "static_homepage":
				        break;
			        case "blog_page":
				        break;
		        }*/
	        }else{
		        //We are NOT in some sort of homepage
		        if(!is_archive() && !is_search() && isset($post_id)){
			        //We are in a common page
			        $current_post_type = get_post_type($post_id);
			        if (!isset($post_id) || $post_id == 0 || !$current_post_type) return;
			        if(in_array($current_post_type, $allowed_locations)) {
				        waboot_breadcrumb_trail($args);
			        }
		        }else{
			        //We are in some sort of archive
			        $show_bc = false;
			        if(is_tag() && in_array('tag',$allowed_locations)){
				        $show_bc = true;
			        }elseif(is_tax() && in_array('tax',$allowed_locations)){
				        $show_bc = true;
			        }elseif(is_archive() && in_array('archive',$allowed_locations)){
				        $show_bc = true;
			        }
			        if($show_bc) waboot_breadcrumb_trail($args);
		        }
	        }
        }
    }
endif;

if(!function_exists("waboot_topnav_wrapper")):
    function waboot_topnav_wrapper(){
        $social_position = of_get_option('waboot_social_position');
        $social_position_class = $social_position == "topnav-left" ? "pull-left" : "pull-right";
        $topnavmenu_position = of_get_option('waboot_topnavmenu_position');
        $topnavmenu_position_class = $topnavmenu_position == "left" ? "pull-left" : "pull-right";
        $has_menu = has_nav_menu('top');
        $must_display_topnav = (is_active_sidebar('topbar') || (($social_position == 'topnav-right' || $social_position == 'topnav-left') && of_get_option("social_position_none") != 1) || $has_menu) ? true : false;

        if ($must_display_topnav):
            ?>
            <!-- Navbar: Begin -->
            <div id="topnav-wrapper">
                <div id="topnav-inner" class="<?php echo of_get_option('waboot_topnav_width', 'container-fluid'); ?> ">

                    <div class="<?php echo $social_position_class; ?>">
                        <?php get_template_part('/templates/parts/social-widget'); ?>
                    </div>

                    <div class="<?php echo $topnavmenu_position_class; ?>">
                        <?php if ($has_menu) get_template_part('/templates/parts/menu', 'top'); ?>
                    </div>

                    <?php dynamic_sidebar('topbar'); ?>
                </div>
            </div>
            <!-- Navbar: End -->
        <?php
        endif;
    }
endif;

if(!function_exists("wbft_the_contact_form")):
	function wbft_the_contact_form(){
		global $post;
		$rand_id = call_user_func(function(){
			$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$charactersLength = strlen($characters);
			$randomString = '';
			for ($i = 0; $i < 5; $i++) {
				$randomString .= $characters[rand(0, $charactersLength - 1)];
			}
			return $randomString;
		});
		$fields = array(
			array(
				'id' => 'name',
				'order' => 0,
				'html' => '<div class="form-group col-sm-6"><label for="name" class=" control-label">'.__('Name', 'waboot').'*</label><input id="name" type="text" class="form-control" name="from[name]" data-field data-validation="!empty"></div>'
			),
			array(
				'id' => 'surname',
				'order' => 1,
				'html' => '<div class="form-group col-sm-6"><label for="surname" class=" control-label">'.__('Surname', 'waboot').'*</label><input id="surname" type="text" class="form-control" name="from[surname]" data-field data-validation="!empty"></div>'
			),
			array(
				'id' => 'phone',
				'order' => 2,
				'html' => '<div class="form-group col-sm-6"><label for="phone" class=" control-label">'.__('Phone', 'waboot').'</label><input id="phone" type="text" class="form-control" name="from[phone]" data-field></div>'
			),
			array(
				'id' => 'email',
				'order' => 3,
				'html' => '<div class="form-group col-sm-6"><label for="email" class=" control-label">'.__('Email', 'waboot').'*</label><input id="email" type="text" class="form-control" name="from[email]" data-field data-validation="!empty"></div>'
			),
			array(
				'id' => 'message',
				'order' => 4,
				'html' => '<div class="form-group col-sm-12"><label for="message" class="control-label">'.__('Message', 'waboot').'*</label><textarea id="message" class="form-control" name="message" rows="5" data-field data-validation="!empty"></textarea></div>'
			),
			array(
				'id' => 'privacy',
				'order' => 5,
				'html' => call_user_func(function(){
					$option_value = \WBF\modules\options\of_get_option("contact_form_privacy_text");
					if(!$option_value || $option_value == ""){
						$output = '<input name="privacy" type="checkbox" value="1" data-field data-validation="checked"><label for="privacy">&nbsp'.__("By submitting this form you agree to our terms and our privacy policy.").'</label>';
					}else{
						$output = '<input name="privacy" type="checkbox" value="1" data-field data-validation="checked"><label for="privacy">&nbsp'.$option_value.'</label>';
					}
					return $output;
				})
			),
			array(
				'id' => 'submit',
				'order' => 6,
				'html' => '<div class="form-group col-sm-12"><button type="submit" class="btn btn-primary">'.__('Send', 'waboot').'</button></div>'
			)
		);
		//User-generated fields?
		$fields = apply_filters("wbft/contact_form/fields",$fields);
		//Apply filters to $fields html
		foreach($fields as $k => $f){
			$fields[$k]['html'] = apply_filters("wbft/contact_form/field/{$f['id']}/tpl",$f['html']);
		}
		?>
		<form id="wb-contact-form" method="post" enctype="multipart/form-data" data-contactForm>
			<input type="hidden" name="pass_id" value="<?php echo $rand_id ?>">
			<input type="hidden" name="fromID" value="<?php echo $post->ID ?>">
			<?php wp_nonce_field( 'send-mail_'.$rand_id ); ?>
			<?php echo apply_filters("wbft/contact_form/tpl",wbft_contact_form_tpl($fields)); ?>
		</form>
		<?php
	}
endif;

if(!function_exists("wbft_contact_form_tpl")):
	/**
	 * Display contact form fields one by one
	 * @param Array $fields an array of fields
	 * @param Array $args of display options. Currently supported options: field_before, field_after
	 *
	 * @return string
	 */
	function wbft_contact_form_tpl($fields,$args = array()){
		$args = wp_parse_args($args,array(
			'field_before' => '',
			'field_after' => ''
		));
		$return_string = "";
		//Reorder fields
		usort($fields,function($a,$b){
			if($a['order'] == $b['order']) return 0;
			return ($a['order'] < $b['order']) ? -1 : 1;
		});
		//Render fields:
		foreach($fields as $f){
			$return_string.= $args['field_before'];
			$return_string.= $f['html'];
			$return_string.= $args['field_after'];
		}
		//Render error tpl:
		$error_message_tpl = '<script type="text/template" data-messageTPL>';
		$error_message_tpl .= apply_filters("wbft/contact_form/messages/std_error/tpl",'<div class="<%= msgclass %>"><%= msg %></div>');
		$error_message_tpl .= '</script>';
		$return_string .= $error_message_tpl;
		//And...
		return $return_string;
	}
endif;

// ###############################
// ###############################
// POST FORMAT HELPERS
// ###############################
// ###############################

/* Post Format Gallery */
if ( ! function_exists('waboot_gallery_format')) :
	function waboot_gallery_format() {
		$output = $images_ids = '';

		if ( function_exists( 'get_post_galleries' ) ) {
			$galleries = get_post_galleries( get_the_ID(), false );

			if ( empty( $galleries ) ) return false;

			if ( isset( $galleries[0]['ids'] ) ) {
				foreach ( $galleries as $gallery ) {
					// Grabs all attachments ids from one or multiple galleries in the post
					$images_ids .= ( '' !== $images_ids ? ',' : '' ) . $gallery['ids'];
				}

				$attachments_ids = explode( ',', $images_ids );
				// Removes duplicate attachments ids
				$attachments_ids = array_unique( $attachments_ids );
			} else {
				$attachments_ids = get_posts( array(
					'fields'         => 'ids',
					'numberposts'    => 999,
					'order'          => 'ASC',
					'orderby'        => 'menu_order',
					'post_mime_type' => 'image',
					'post_parent'    => get_the_ID(),
					'post_type'      => 'attachment',
				) );
			}
		} else {
			$pattern = get_shortcode_regex();
			preg_match( "/$pattern/s", get_the_content(), $match );
			$atts = shortcode_parse_atts( $match[3] );

			if ( isset( $atts['ids'] ) )
				$attachments_ids = explode( ',', $atts['ids'] );
			else
				return false;
		}

		echo '<div id="carousel-gallery-format" class="carousel slide" data-ride="carousel">';
		echo '	<div class="carousel-inner" role="listbox">';
		$i = 0;
		foreach ( $attachments_ids as $attachment_id ) {
			if($i == 0){
				printf( '<div class="item active">%s</div>',
					// esc_url( get_permalink() ),
					wp_get_attachment_image( $attachment_id, 'medium' )
				);
			}else{
				printf( '<div class="item">%s</div>',
					// esc_url( get_permalink() ),
					wp_get_attachment_image( $attachment_id, 'medium' )
				);
			}
			$i++;
		}
		echo '	</div> <!-- .carousel-inner -->';
		echo '<!-- Controls -->
              <a class="left carousel-control" href="#carousel-gallery-format" role="button" data-slide="prev">
                <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
              </a>
              <a class="right carousel-control" href="#carousel-gallery-format" role="button" data-slide="next">
                <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
              </a>
        ';
		echo '</div> <!-- #carousel-gallery-format -->';

		return $output;
	}
endif;
/* End Post Format Gallery */

/* Post Format Video */
function waboot_video_embed_html($video) {
	return "<div class='wb_post_video'>{$video}</div>";
}
add_filter( 'embed_oembed_html', 'waboot_video_embed_html' );

if(!function_exists('waboot_get_first_video' )):
	function waboot_get_first_video() {
		$first_oembed  = '';
		$custom_fields = get_post_custom();

		foreach ( $custom_fields as $key => $custom_field ) {
			if ( 0 !== strpos( $key, '_oembed_' ) ) continue;

			$first_oembed = $custom_field[0];

			$video_width  = (int) apply_filters( 'wb_video_width', 100 );
			$video_height = (int) apply_filters( 'wb_video_height', 480 );

			$first_oembed = preg_replace( '/<embed /', '<embed wmode="transparent" ', $first_oembed );
			$first_oembed = preg_replace( '/<\/object>/','<param name="wmode" value="transparent" /></object>', $first_oembed );

			$first_oembed = preg_replace( "/width=\"[0-9]*\"/", "width={$video_width}%", $first_oembed );
			$first_oembed = preg_replace( "/height=\"[0-9]*\"/", "height={$video_height}", $first_oembed );

			break;
		}

		return ( '' !== $first_oembed ) ? $first_oembed : false;
	}
endif;
/* End Post Format Video */

if(!function_exists('waboot_the_trimmed_excerpt')):
	/**
	 * A version of the_excerpt() that applies the trim function to the predefined excerpt as well
	 *
	 * @param bool $length
	 * @param bool|null $more
	 * @param null $post_id
	 * @param string $use is "content_also" then the content will be trimmed if the excerpt is empty
	 */
	function waboot_the_trimmed_excerpt($length = false,$more = null,$post_id = null, $use = "excerpt_only"){
		wbft_the_trimmed_excerpt($length,$more,$post_id,$use);
	}
endif;

/* Post Format Link */
if ( ! function_exists('waboot_link_format_helper')) :
	/**
	 * Returns the first post link and/or post content without the link.
	 * Used for the "Link" post format.
	 *
	 * @since 0.1.0
	 * @param string $output "link" or "post_content"
	 * @return string Link or Post Content without link.
	 */
	function waboot_link_format_helper( $output = false ) {

		if ( ! $output )
			_doing_it_wrong( __FUNCTION__, __( 'You must specify the output you want - either "link" or "post_content".', 'waboot' ), '1.0.1' );

		$post_content = get_the_content();

		$link = preg_match( '/<a\s[^>]*?href=[\'"](.+?)[\'"][^>]*>[^>]*>/is', $post_content, $matches );
		if($link){
			$link_url = $matches[1];
			$post_content = substr( $post_content, strlen( $matches[0] ) );
			if(!$post_content) $post_content = "";
		}

		// Return the first link in the post content
		if ( 'link' == $output ){
			if($link){
				return $link_url;
			}else{
				return "";
			}
		}

		// Return the post content without the first link
		if ( 'post_content' == $output )
			return $post_content;
	}
endif;
/* End Post Format Link */

