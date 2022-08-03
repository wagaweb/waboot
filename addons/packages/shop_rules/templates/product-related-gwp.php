<?php

use Waboot\addons\packages\shop_rules\rule_params\BuyXGetY;
use Waboot\addons\packages\shop_rules\ShopRule;

/** @var ShopRule $r */
/** @var WP_Post[] $gwpProducts */

global $product;

$params = $r->getBuyXgetYParam();
$message = $params->getProductPageMessage();
$layout = $params->getProductPageMessageLayout();

if ($layout === BuyXGetY::PRODUCT_PAGE_MESSAGE_LAYOUT_HIDDEN) {
    return;
}

?>

<div class="gwp-container" data-shop-rule="<?php echo $r->getKey(); ?>">
    <?php if ($layout === BuyXGetY::PRODUCT_PAGE_MESSAGE_LAYOUT_LIST) : ?>
        <?php foreach ($gwpProducts as $gwpProduct) : ?>
            <div class="product-gwp__item">
                <div class="product-gwp__image">
                    <img src="<?php echo get_the_post_thumbnail_url($gwpProduct, 'medium'); ?>" alt="">
                </div>
                <div class="product-gwp__text">
                    <h6>Omaggio</h6>
                    <p><?php echo $message; ?><br>
                        <?php echo get_the_title($gwpProduct); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php elseif ($layout === BuyXGetY::PRODUCT_PAGE_MESSAGE_LAYOUT_MESSAGE) : ?>
        <p class="gwp-container__message"><?php echo $message; ?></p>
    <?php endif; ?>
</div>

