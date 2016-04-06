<?php

require_once("hooks/entry-header.php");
require_once("hooks/entry-footer.php");
require_once("hooks/layout.php");
require_once("hooks/ajax.php");

if(!function_exists('waboot_behaviors_cpts_blacklist')):
    /**
     * Puts some custom post types into blacklist (in these post types the behavior will never be displayed)
     * @param $blacklist
     * @return array
     */
    function waboot_behaviors_cpts_blacklist($blacklist){
        $blacklist[] = "metaslider";
        return $blacklist;
    }
    add_filter("wbf_behaviors_cpts_blacklist","waboot_behaviors_cpts_blacklist");
endif;

if(!function_exists('waboot_set_default_components')):
    /**
     * Set the default components
     * @param $components
     * @return array
     */
    function waboot_set_default_components($components){
        $components[] = "slideshow";
        $components[] = "colorbox";

        return $components;
    }
    add_filter("wbf_default_components","waboot_set_default_components");
endif;

if(!function_exists('waboot_mainnav_class')):
    function waboot_mainnav_class($classes){
        $options = of_get_option( 'waboot_navbar_align' );
        $classes[] = $options;

        return implode(' ', $classes);
    }
    add_filter("waboot_mainnav_class","waboot_mainnav_class");
endif;

if(!function_exists('waboot_ignore_sticky_post_in_archives')):
	function waboot_ignore_sticky_post_in_archives($query){
		if(is_category() || is_tag() || is_tax()) {
			$query->set("post__not_in",get_option( 'sticky_posts', array() ));
		}
	}
	add_action( 'pre_get_posts', 'waboot_ignore_sticky_post_in_archives' );
endif;

if(!is_admin() && !function_exists("waboot_mobile_body_class") && class_exists("WBF") && function_exists("WBF::get_mobile_detect")):
	/**
	 * Adds mobile classes to body
	 */
	function waboot_mobile_body_class($classes){
		$md = WBF::getInstance()->get_mobile_detect();
		if($md->isMobile()){
			$classes[] = "mobile";
			if($md->is_ios()) $classes[] = "mobile-ios";
			if($md->is_android()){
				$classes[] = "mobile-android";
				$classes[] = "mobile-android-".$md->version('Android');
			}
			if($md->is_windows_mobile()) $classes[] = "mobile-windows";
			if($md->isTablet()) $classes[] = "mobile-tablet";
			if($md->isIphone()){
				$classes[] = "mobile-iphone";
				$classes[] = "mobile-iphone-".$md->version('IPhone');
			}
			if($md->isIpad()){
				$classes[] = "mobile-ipad";
				$classes[] = "mobile-ipad-".$md->version('IPad');
			}
			if($md->is('Kindle')) $classes[] = "mobile-kindle";
			if($md->is('Samsung')) $classes[] = "mobile-samsung";
			if($md->is('SamsungTablet')) $classes[] = "mobile-samsungtablet";
		}else{
			$classes[] = "desktop";
		}
		return $classes;
	}
	add_filter('body_class','waboot_mobile_body_class');
endif;

if(!function_exists('wbft_favicons')):
	/**
	 * Display the favicons
	 */
	function wbft_favicons(){
		$icon = of_get_option("favicon_icon");
		$iphone = of_get_option("favicon_apple120");
		$ipad = of_get_option("favicon_apple152");
		?>
		<?php if($icon && !empty($icon)) : ?>
			<link rel="icon" href="<?php echo $icon ?>" type="image/x-icon"/>
			<link rel="shortcut icon" href="<?php echo $icon ?>" type="image/x-icon"/>
		<?php endif; ?>
		<?php if($iphone && !empty($iphone)) : ?>
			<link rel="apple-touch-icon" sizes="120x120" href="<?php echo $iphone ?>">
		<?php endif; ?>
		<?php if($ipad && !empty($ipad)) : ?>
			<link rel="apple-touch-icon" sizes="152x152" href="<?php echo $ipad ?>">
		<?php endif; ?>
		<?php
	}
	add_action('wp_head', 'wbft_favicons');
endif;

if(!function_exists('waboot_rg_ls') && class_exists('\WBF\admin\License_Manager')):
	function waboot_rg_ls(){
		require_once("ls.php");
		\WBF\admin\License_Manager::register_theme_license(Waboot_LS::getInstance("waboot",['suffix'=>true]));
	}
	add_action("wbf_init","waboot_rg_ls");
endif;

/*
 * COMPILER
 */

if(is_multisite() && !function_exists("wbft_multisite_output_stylesheet_name")):
	add_filter("wbft/compiler/output/filename","wbft_multisite_output_stylesheet_name");
	/**
	 * Alter the compiled stylesheet name in multisite environment
	 *
	 * @param $filename
	 *
	 * @return string
	 */
	function wbft_multisite_output_stylesheet_name($filename){
		if(wbft_wbf_in_use()){
			$blogname = wbf_get_sanitized_blogname();
		}else{
			$blogname = sanitize_title_with_dashes(get_bloginfo("name"));
		}
		return $blogname."-".$filename;
	}
endif;

/*
 * CONTACT FORM
 */

if(!function_exists('wbft_add_received_mails_submenu')):
	function wbft_add_received_mails_submenu($parent_slug){
		$waboot_mail_view = add_submenu_page( $parent_slug, __( "Inbox", "waboot" ), __( "Inbox", "waboot" ), "edit_theme_options", "waboot_inbox", "wbft_add_received_mails_page" );
	}
	if(wbft_wbf_in_use())
		add_action( 'wbf_admin_submenu', 'wbft_add_received_mails_submenu', 99 );
	else
		add_action('admin_menu', function(){
			add_submenu_page('tools.php', __( "Inbox", "waboot" ), __( "Inbox", "waboot" ), 'manage_options', "waboot_inbox", "wbft_add_received_mails_page");
		});
endif;

if(!function_exists('wbft_add_received_mails_page')):
	function wbft_add_received_mails_page($parent_slug){
		?>
		<div class="wrap">
			<h2><?php _e("Inbox","waboot"); ?></h2>
			<div id="waboot-received-mails-view">
				<script type="text/template" id="waboot-received-mails-tpl">
					<table class="wp-list-table widefat fixed striped waboot-inbox">
						<thead>
						<tr>
							<th scope="col" id="cb" class="manage-column column-cb check-column" style="">
								<label class="screen-reader-text" for="cb-select-all-1"><?php _e("Select all","waboot"); ?></label><input id="cb-select-all-1" type="checkbox">
							</th>
							<th scope="col" id="recipient" class="manage-column column-recipient sortable desc" style="">
								<a href="#"><span><?php _e("Recipient","waboot"); ?></span><span class="sorting-indicator"></span></a>
							</th>
							<th scope="col" id="sender" class="manage-column column-sender sortable desc" style="">
								<a href="#"><span><?php _e("From","waboot"); ?></span><span class="sorting-indicator"></span></a>
							</th>
							<th scope="col" id="source" class="manage-column column-source sortable desc" style="">
								<a href="#"><span><?php _e("Source","waboot"); ?></span><span class="sorting-indicator"></span></a>
							</th>
							<th scope="col" id="date" class="manage-column column-date" style="">
								<a href="#"><span><?php _e("Date submitted","waboot"); ?></span><span class="sorting-indicator"></span></a>
							</th>
							<th scope="col" id="status" class="manage-column column-status num" style="">
								<a href="#"><span><?php _e("Received status","waboot"); ?></span><span class="sorting-indicator"></span></a>
							</th>
						</tr>
						</thead>
						<tbody id="the-list" data-wp-lists="list:mail">
							<% _.each(mails,function(m, k){ %>
							<tr id="mail-<%= m.id %>">
								<th scope="row" class="check-column">
									<label class="screen-reader-text" for="user_<%= m.id %>"><?php _e("Select this mail","waboot"); ?></label>
									<input type="checkbox" name="mails[]" id="mail_<%= m.id %>" class="mail" value="<%= m.id %>">
								</th>
								<td class="recipient column-recipient">
									<strong><%= m.recipient %></strong><br>
									<div class="row-actions">
										<span class="view">
											<a class="submitview" href="#" data-view-content-of="<%= m.id %>"><?php _e("View","waboot"); ?></a>
											&nbsp;|&nbsp;
										</span>
										<span class="delete">
											<a class="submitdelete" href="#" data-delete="<%= m.id %>"><?php _e("Delete","waboot"); ?></a>
										</span>
									</div>
								</td>
								<td class="sender column-sender"><%= m.sender_mail %></td>
								<td class="source column-source">
									<a href="<%= wbData.wpurl %>/index.php?p=<%= m.sourceid %>"><%= m.sourceid %></a>
								</td>
								<td class="date column-date">
									<%= m.date_created %>
								</td>
								<td class="status column-status">
									<span class="status-<%= m.status %>"><%= m.status %></span>
								</td>
								<div id="mail-<%= m.id %>-content" title="<%= m.subject %>" data-content-of="<%= m.id %>" style="display: none;">
									<%= m.content.replace(/(?:\r\n|\r|\n)/g, '<br />') %>
								</div>
							</tr>
							<% }); %>
						</tbody>
						<tfoot>
						<tr>
							<th scope="col" id="cb" class="manage-column column-cb check-column" style="">
								<label class="screen-reader-text" for="cb-select-all-1"><?php _e("Select all","waboot"); ?></label><input id="cb-select-all-1" type="checkbox">
							</th>
							<th scope="col" id="username" class="manage-column column-username sortable desc" style="">
								<a href="#"><span><?php _e("Recipient","waboot"); ?></span><span class="sorting-indicator"></span></a>
							</th>
							<th scope="col" id="name" class="manage-column column-name sortable desc" style="">
								<a href="#"><span><?php _e("From","waboot"); ?></span><span class="sorting-indicator"></span></a>
							</th>
							<th scope="col" id="email" class="manage-column column-email sortable desc" style="">
								<a href="#"><span><?php _e("Source","waboot"); ?></span><span class="sorting-indicator"></span></a>
							</th>
							<th scope="col" id="role" class="manage-column column-role" style="">
								<a href="#"><span><?php _e("Date submitted","waboot"); ?></span><span class="sorting-indicator"></span></a>
							</th>
							<th scope="col" id="posts" class="manage-column column-posts num" style="">
								<a href="#"><span><?php _e("Received status","waboot"); ?></span><span class="sorting-indicator"></span></a>
							</th>
						</tr>
						</tfoot>
					</table>
					<div class="tablenav bottom">
						<div class="tablenav-pages">
							<span class="displaying-num"><%= mails_count %> <?php _e("email/s","waboot") ?></span>
							<span class="pagination-links">
								<a class="first-page <% if(current_page === 1){ %>disabled<% } %>" title="<?php _e("Go back to the first page","waboot") ?>" href="#">«</a>
								<a class="prev-page <% if(current_page === 1){ %>disabled<% } %>" title="<?php _e("Go back to the previous page","waboot") ?>" href="#">‹</a>
								<span class="paging-input"><%= current_page %> di <span class="total-pages"><%= pages_count %></span></span>
								<a class="next-page <% if(current_page === pages_count){ %>disabled<% } %>" title="<?php _e("Go back to the next page","waboot") ?>" href="#">›</a>
								<a class="last-page <% if(current_page === pages_count){ %>disabled<% } %>" title="<?php _e("Go back to the last page","waboot") ?>" href="#">»</a>
							</span>
						</div>
						<br class="clear">
					</div>
				</script>
			</div>
		</div>
		<?php
	}
endif;