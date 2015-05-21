<?php

add_action('wp_ajax_wbft_send_contact_email', 'wbft_send_contact_email');
add_action('wp_ajax_nopriv_wbft_send_contact_email', 'wbft_send_contact_email');

function wbft_send_contact_email(){
	$save_mail = function($data){
		global $wpdb;
		$result = $wpdb->insert($wpdb->prefix . "wb_mails",$data);
		return $result;
	};

	//Looking for $_POST params setter? Looking in /sources/js/controllers/contactForm.js
	$mail_data = apply_filters("wbft/contact_form/mail/data",$_POST);
	$save_mail_data = apply_filters("wbft/contact_form/mail/save/data",$_POST);

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

function wbft_ajax_out($var){
	if(is_array($var)){
		echo json_encode($var);
	}else{
		echo $var;
	}
	die;
}