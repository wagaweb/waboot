<?php
/**
Component Name: Owl Carousel
Description: Owl Carousel 2.0 component
Version: 2.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
*/

function owlcarousel_scripts() {
    wp_enqueue_script('owlcarousel-script', get_template_directory_uri() . '/wbf/vendor/owlcarousel/owl.carousel.min.js', array('jquery'), false, false);
    wp_enqueue_script('owlcarousel-custom-script', get_template_directory_uri() . '/wbf/vendor/owlcarousel/owl.carousel-custom.js', array('jquery'), false, false);
    wp_enqueue_style('owlcarousel-style', get_template_directory_uri() . '/wbf/vendor/owlcarousel/assets/owl.carousel.css');
}

add_action( 'wp_enqueue_scripts', 'owlcarousel_scripts' );