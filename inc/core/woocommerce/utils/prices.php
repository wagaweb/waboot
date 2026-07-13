<?php

namespace Waboot\inc\core\woocommerce\utils;

/**
 * @param float $price
 * @return float
 */
function roundTo5Cents(float $price): float {
    // Multiply by 20 to shift to the nearest 5 cents
    $scaled = $price * 20;
    // Round to the nearest integer
    $rounded = round($scaled);
    // Divide by 20 to get back to the original scale
    $result = $rounded / 20;
    // Format the result with 2 decimal places and comma as separator
    // return number_format($result, 2, ',', '');
    return $result;
}