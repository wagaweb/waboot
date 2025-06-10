<?php

namespace Waboot\addons\packages\checkout\hooks\advanced;

use function Waboot\addons\packages\checkout\printCustomCouponWrapper;
use function Waboot\addons\packages\checkout\printCustomCouponWrapperJS;

add_action('woocommerce_checkout_before_terms_and_conditions', function(){
    printCustomCouponWrapper();
    printCustomCouponWrapperJS();
}, 20);