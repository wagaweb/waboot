<?php

namespace Waboot\addons\packages\topbar;

use Waboot\inc\core\mvc\HTMLView;
use function Waboot\addons\getAddonDirectory;

add_action('customize_register', function (\WP_Customize_Manager $wpCustomize) {
    $wpCustomize->add_section('topbar', array(
        'title' => __('Topbar'),
        'priority' => 10,
    ));
    $wpCustomize->add_setting('topbarText', array(
        'type' => 'option',
        'transport' => 'refresh',
        'default' => 'Get 20% OFF on your first order. Subscribe to our newsletter and get your coupon!',
    ));
    $wpCustomize->add_control('topbarText', array(
        'type' => 'text',
        'section' => 'topbar',
        'settings' => 'topbarText',
        'label' => __('Text', LANG_TEXTDOMAIN),
        'priority' => 10,
    ));
});

add_action('waboot/layout/header', function(){
    $v = new HTMLView(getAddonDirectory('topbar').'/templates/topbarTemplate.php',false);

    $topbarText = get_option('topbarText');
    if(!\is_string($topbarText)){
        $topbarText = '';
    }

    $v->clean()->display([
        'topbarText' => $topbarText
    ]);
},9);
