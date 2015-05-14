<?php

require_once("hooks/entry-header.php");
require_once("hooks/entry-footer.php");
require_once("hooks/layout.php");
require_once("hooks/ajax.php");

if(!function_exists('waboot_do_site_title')):
    /**
     * Displays site title at top of page
     * @since 0.1.0
     */
    function waboot_do_site_title() {
        // Use H1
        $element = 'h1';
        // Title content that goes inside wrapper
        $site_name = sprintf( '<a href="%s" title="%s" rel="home">%s</a>', trailingslashit( home_url() ), esc_attr( get_bloginfo( 'name' ) ), get_bloginfo( 'name' ) );
        // Put it all together
        $title = '<' . $element . ' id="site-title" class="site-title">' . $site_name . '</' . $element .'>';
        // Echo the title
        echo apply_filters( 'waboot_site_title_content', $title );
    }
    add_action( 'waboot_site_title', 'waboot_do_site_title' );
endif;

if(!function_exists('waboot_do_site_description')):
    /**
     * Displays site description at top of page
     * @since 0.1.0
     */
    function waboot_do_site_description() {
        // Use H2
        $element = 'h2';
        // Put it all together
        $description = '<' . $element . ' id="site-description" class="site-description">' . esc_html( get_bloginfo( 'description' ) ) . '</' . $element . '>';
        // Echo the description
        echo apply_filters( 'waboot_site_description_content', $description );
    }
    add_action( 'waboot_site_description', 'waboot_do_site_description' );
endif;

if(!function_exists('waboot_do_archive_page_title')):
    /**
     * Display page title on archive pages
     * @since 0.1.0
     */
    function waboot_do_archive_page_title($prefix = "",$suffix = "") {
	    waboot_archive_page_title($prefix,$suffix,true);
    }
    add_action( 'waboot_archive_page_title', 'waboot_do_archive_page_title', 10, 2 );
endif;

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

if(!function_exists('wbft_parse_contact_form_data')):
	/**
	 * Parse the contact form data before sending the email
	 * @param $data
	 *
	 * @return array
	 */
	function wbft_parse_contact_form_data($data){
		$to = $data['to'];
		$subject = $data['subject'];
		$from = $data['from'];
		$message = apply_filters("wbft/contact_form/mail/content",$data);
		$headers = array(
			sprintf("From: %s <%s>",$from['name']." ".$from['surname'],$from['email'])
		);
		return array(
			'to' => $to,
			'subject' => $subject,
			'message' => $message,
			'header' => $headers
		);
	}
	add_filter("wbft/contact_form/mail/data","wbft_parse_contact_form_data");
endif;

if(!function_exists('wbft_parse_contact_form_date_for_saving')):
	/**
	 * Parse the contact form data before saving the email
	 * @param $data
	 *
	 * @return array
	 */
	function wbft_parse_contact_form_date_for_saving($data){
		$to_id = $data['to_id'];
		$subject = $data['subject'];
		$message = apply_filters("wbft/contact_form/mail/content",$data);
		$from = $data['from'];
		$post_id = $data['post_id'];
		$now = new \DateTime();
		$data = array(
			'content' => $message,
			'to' => $to_id,
			'subject' => $subject,
			'from_mail' => $from['email'],
			'from_data' => array(
				'name' => $from['name'],
				'phone' => $from['phone'],
			),
			'fromID' => $post_id,
			'date_created' => $now->format("Y-m-d")
		);
		return $data;
	}
	add_filter("wbft/contact_form/mail/save/data",'wbft_parse_contact_form_date_for_saving');
endif;

if(!function_exists('wbft_parse_contact_form_mail_content')):
	/**
	 * Generate the contact form mail content
	 * @param $data
	 *
	 * @return string
	 */
	function wbft_parse_contact_form_mail_content($data){
		$from = $data['from'];
		$post_id = $data['post_id'];

		$message = $data['message'];
		$message.= "\r\n";
		$message.= "--------------------------";
		$message.= "\r\n";
		$message.= __("Source link:","waboot")." ".get_the_permalink($post_id);
		$message.= "\r\n";
		$message.= __("Client Name:","waboot")." ".$from['name']." ".$from['surname'];
		$message.= "\r\n";
		$message.= __("Client Phone:","waboot")." ".$from['phone'];

		return $message;
	}
	add_filter("wbft/contact_form/mail/content","wbft_parse_contact_form_mail_content");
endif;