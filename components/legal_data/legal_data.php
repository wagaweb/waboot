<?php
/**
Component Name: Legal Informations
Description: Provides four shortcodes to display legal informations throughout your website (i.e. name, address, mail, tel )
Category: Utilities
Tags: Legal, Address, Company, Tel, Info
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
 */

class Legal_Data extends \Waboot\Component{

	/**
	 * This method will be executed at Wordpress startup (every page load)
	 */
	public function setup(){
		parent::setup();
		//Do stuff...
	}



	/**
	 * This method will be executed on the "wp" action in pages where the component must be loaded
	 */
	public function run(){
		parent::run();

		add_shortcode("wb_legal_name", function(){
			ob_start();
			$company = empty(\Waboot\functions\get_option($this->name.'_company_name')) ? "Company Name" : \Waboot\functions\get_option($this->name.'_company_name');
			echo $company;
			$return_string = trim( preg_replace( "|[\r\n\t]|", "", ob_get_clean() ) );
			return $return_string;
		});
		add_shortcode("wb_legal_address", function(){
			ob_start();
			$address = empty(\Waboot\functions\get_option($this->name.'_address')) ? "Company Address" : \Waboot\functions\get_option($this->name.'_address');
			echo $address;
			$return_string = trim( preg_replace( "|[\r\n\t]|", "", ob_get_clean() ) );
			return $return_string;
		});
		add_shortcode("wb_legal_mail", function(){
			ob_start();
			$mail = empty(\Waboot\functions\get_option($this->name.'_mail')) ? "Company Mail" : \Waboot\functions\get_option($this->name.'_mail');
			echo $mail;
			$return_string = trim( preg_replace( "|[\r\n\t]|", "", ob_get_clean() ) );
			return $return_string;
		});
		add_shortcode("wb_legal_tel", function(){
			ob_start();
			$tel = empty(\Waboot\functions\get_option($this->name.'_tel')) ? "Company Tel" : \Waboot\functions\get_option($this->name.'_tel');
			echo $tel;
			$return_string = trim( preg_replace( "|[\r\n\t]|", "", ob_get_clean() ) );
			return $return_string;
		});
	}


	/**
	 * Register component scripts (called automatically)
	 */
	public function scripts(){
		//wp_register_script('component-header_fixed', $this->directory_uri . '/assets/dist/js/headerFixed.js', ['jquery'], false, true);

		/*wp_localize_script('component-header_fixed', 'wbHeaderFixed', array(
			'company_name' => $company,
			'address' => $address,
			'mail' => $mail,
			'tel' => $tel,
		) );
		wp_enqueue_script('component-header_fixed');*/
	}



	/**
	 * Register component styles (called automatically)
	 */
	public function styles(){

		//wp_enqueue_style('component-header_fixed-style', $this->directory_uri . '/assets/dist/css/headerFixed.css');
	}



	/**
	 * Register component widgets (called automatically).
	 *
	 * @hooked 'widgets_init'
	 */
	public function widgets(){
		//register_widget("sampleWidget");
	}

	/**
	 * This is an action callback.
	 *
	 * Here you can use WBF Organizer to set component options
	 */
	public function register_options() {
		parent::register_options();


		/*

		$orgzr = \WBF\modules\options\Organizer::getInstance();

		$orgzr->set_group($this->name."_component");

		$orgzr->add_section("legal",_x("Legal","Theme options section","waboot"));

		$orgzr->add([
			'name' => 'Legal Information',
			'desc' => __( 'Edit default options for Header Fixed post type', 'waboot' ),
			'type' => 'info'
		], "legal");

		$orgzr->update($this->name.'_company_name',[
			'name' => __( 'Class to fix', 'waboot' ),
			'desc' => __( 'Select the class you want to fix. ', 'waboot' ),
			'id'   => $this->name.'_company_name',
			'std'  => '',
			'type' => 'text'
		],"legal");

		$orgzr->update($this->name.'_address',[
			'name' => __( 'Mode', 'waboot' ),
			'desc' => __( 'Choose if you want the class to be fixed from the beginning, after a breakpoint or on scroll up', 'waboot' ),
			'id'   => $this->name.'_address',
			'std'  => '',
			'type' => 'text',
		],"legal");

		$orgzr->update($this->name.'_mail',[
			'name' => __( 'Style Before - Color', 'waboot' ),
			'desc' => __( ' ', 'waboot' ),
			'id'   => $this->name.'_mail',
			'std'  => '',
			'type' => 'text'
		],"legal");

		$orgzr->update($this->name.'_tel',[
			'name' => __( 'Style Before - Padding', 'waboot' ),
			'desc' => __( ' ', 'waboot' ),
			'id'   => $this->name.'_tel',
			'std'  => '',
			'type' => 'text'
		],"legal");

		$orgzr->reset_group();
		$orgzr->reset_section();

		*/





		/*

		$company = \Waboot\functions\get_option($this->name.'_company_name');
		$address = \Waboot\functions\get_option($this->name.'_address');
		$mail = \Waboot\functions\get_option($this->name.'_mail');
		$tel = \Waboot\functions\get_option($this->name.'_tel');

		*/
	}

	/**
	 * This is a filter callback. You can't use WBF Organizer.
	 *
	 * @param $options
	 *
	 * @return array|mixed
	 */
	public function theme_options($options){
		$options = parent::theme_options($options);

		$options[] = array(
			'name' => 'Legal Information',
			'desc' => __( '', 'waboot' ),
			'type' => 'info'
		);
		$options[] = array(
			'name' => __( 'Name', 'waboot' ),
			'desc' => __( 'Your Company Name', 'waboot' ),
			'id'   => $this->name.'_company_name',
			'std'  => '',
			'type' => 'text'
		);
		$options[] = array(
			'name' => __( 'Address', 'waboot' ),
			'desc' => __( 'Your Company Address', 'waboot' ),
			'id'   => $this->name.'_address',
			'std'  => '',
			'type' => 'text',
		);
		$options[] = array(
			'name' => __( 'Mail', 'waboot' ),
			'desc' => __( 'choose an email address', 'waboot' ),
			'id'   => $this->name.'_mail',
			'std'  => '',
			'type' => 'text'
		);
		$options[] = array(
			'name' => __( 'Tel', 'waboot' ),
			'desc' => __( 'Choose a telephone Number', 'waboot' ),
			'id'   => $this->name.'_tel',
			'std'  => '',
			'type' => 'text'
		);

		return $options;
	}

	public function onActivate(){
		parent::onActivate();
		//Do stuff...
	}

	public function onDeactivate(){
		parent::onDeactivate();
		//Do stuff...
	}
}