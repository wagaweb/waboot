<?php

add_action('wp_ajax_wbft_send_contact_email', 'wbft_send_contact_email');
add_action('wp_ajax_nopriv_wbft_send_contact_email', 'wbft_send_contact_email');

function wbft_send_contact_email(){
	$save_mail = function($data){
		return true;
	};

	$mail_data = apply_filters("wbft/contact_form/mail/data",$_POST);
	$save_mail_data = apply_filters("wbft/contact_form/mail/save/data",$_POST);

	if(wp_mail($mail_data['to'],$mail_data['subject'],$mail_data['message'],$mail_data['headers'])){
		if($save_mail($save_mail_data)){ //todo: fare il salvataggio della mail nel DB
			wbft_ajax_out(2);
		}else{
			wbft_ajax_out(1);
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