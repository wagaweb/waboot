<?php

namespace Waboot\addons\packages\checkout\hooks\base;

use function Waboot\addons\packages\checkout\printCustomCouponWrapper;
use function Waboot\addons\packages\checkout\printCustomCouponWrapperJS;

add_action('woocommerce_review_order_before_payment', function(){
    printCustomCouponWrapper();
    printCustomCouponWrapperJS();
} , 20 );