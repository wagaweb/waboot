<?php

add_action('wp_ajax_wbft_send_contact_email', 'wbft_send_contact_email');
add_action('wp_ajax_nopriv_wbft_send_contact_email', 'wbft_send_contact_email');

add_action('wp_ajax_wbft_delete_contact_email', 'wbft_delete_contact_email');
add_action('wp_ajax_nopriv_wbft_delete_contact_email', 'wbft_delete_contact_email');

function wbft_send_contact_email(){
	/**
	 * Save mail into the db
	 * @param $data
	 *
	 * @return false|int
	 */
	$save_mail = function($data){
		global $wpdb;
		$result = $wpdb->insert($wpdb->prefix . "wb_mails",$data);
		return $result;
	};

	/**
	 * Generate the contact form mail content
	 * @message
	 * @param $post_data ($_POST formatted in /sources/js/controllers/contactForm.js)
	 *
	 * @return string
	 */
	$parse_contact_form_mail_content = function($post_data){
		$from = $post_data['from'];
		$post_id = $post_data['post_id'];

		$message = $post_data['message'];
		$message.= "\r\n";
		$message.= "--------------------------";
		$message.= "\r\n";
		$message.= __("Source link:","waboot")." ".get_the_permalink($post_id);
		$message.= "\r\n";
		$message.= __("Client Name:","waboot")." ".$from['name']." ".$from['surname'];
		$message.= "\r\n";
		$message.= __("Client Email:","waboot")." ".$from['email'];
		$message.= "\r\n";
		$message.= __("Client Phone:","waboot")." ".$from['phone'];

		return $message;
	};

	/**
	 * Parse the contact form data before sending the email
	 * @param $post_data ($_POST formatted in /sources/js/controllers/contactForm.js)
	 *
	 * @return array
	 */
	$parse_contact_form_data = function($post_data) use (&$parse_contact_form_mail_content){
		$to = $post_data['to'];
		$subject = $post_data['subject'];
		$from = $post_data['from'];
		$message = apply_filters("wbft/contact_form/mail/content",$parse_contact_form_mail_content($post_data),$post_data);
		$headers = array(
			sprintf("From: %s <%s>",$from['name']." ".$from['surname'],$from['email'])
		);

		$data = array(
			'to' => $to,
			'subject' => $subject,
			'message' => $message,
			'headers' => $headers
		);

		return $data;
	};

	/**
	 * Parse the contact form data before saving the email
	 * @param $post_data ($_POST formatted in /sources/js/controllers/contactForm.js)
	 *
	 * @return array
	 */
	$parse_contact_form_data_for_saving = function($post_data) use (&$parse_contact_form_mail_content){
		$recipient = $post_data['to'];
		$subject = $post_data['subject'];
		$message = apply_filters("wbft/contact_form/mail/content",$parse_contact_form_mail_content($post_data),$post_data);
		$from = $post_data['from'];
		$post_id = $post_data['post_id'];
		$now = new \DateTime();
		$data = array(
			'content' => $message,
			'recipient' => $recipient,
			'subject' => $subject,
			'sender_mail' => $from['email'],
			'sender_info' => serialize(array(
				'name' => $from['name'],
				'phone' => $from['phone'],
			)),
			'sourceid' => $post_id,
			'date_created' => $now->format("Y-m-d")
		);
		return $data;
	};

	//Looking for $_POST params setter? Looking in /sources/js/controllers/contactForm.js
	$mail_data = apply_filters("wbft/contact_form/mail/data",$parse_contact_form_data($_POST),$_POST);
	$save_mail_data = apply_filters("wbft/contact_form/mail/save/data",$parse_contact_form_data_for_saving($_POST),$_POST);

	if(wp_mail($mail_data['to'],$mail_data['subject'],$mail_data['message'],$mail_data['headers'])){
		$save_mail_data['status'] = 1;
		$save_mail($save_mail_data);
		wbft_ajax_out(2);
	}else{
		$save_mail_data['status'] = 0;
		$save_mail($save_mail_data);
		wbft_ajax_out(0);
	}
}

function wbft_delete_contact_email(){
	global $wpdb;
	$id = $_POST['id'];

	if($id){
		$result = $wpdb->delete( $wpdb->prefix."wb_mails", array( 'id' => $id ) );
		if($result){
			wbft_ajax_out(1);
		}else{
			wbft_ajax_out(0);
		}
	}else{
		wbft_ajax_out(0);
	}
}

function wbft_ajax_out($var){
	if(is_array($var)){
		echo json_encode($var);
	}else{
		echo $var;
	}
	die;
}