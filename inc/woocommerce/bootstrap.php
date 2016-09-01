<?php

namespace Waboot\woocommerce;

global $woocommerce;

if(!isset($woocommerce)) return;

require_once 'template-functions.php';
require_once 'template-tags.php';
require_once 'hooks.php';