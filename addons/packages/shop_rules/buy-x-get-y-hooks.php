<?php

namespace Waboot\addons\packages\shop_rules;

use Waboot\addons\packages\shop_rules\rule_params\BuyXGetY;
use Waboot\addons\packages\shop_rules\rule_params\BuyXGetYItem;

use function Waboot\addons\getAddonDirectory;

/**
 * @return ShopRule[]
 */
function getBuyXGetYRules(): array
{
    return array_filter(fetchShopRules(), function (ShopRule $shopRule): bool {
        return $shopRule->getType() === ShopRule::TYPE_BUY_X_GET_Y;
    });
}

function doesProductMeetTaxFilterCondition(
    \WC_Product $product,
    ShopRuleTaxFilter $tf,
    bool $skipAttributes = false
): bool {
    $variationAttributes = [];
    if ($product->get_type() === 'variation') {
        /** @var \WC_Product_Variation $product */
        /** @var \WC_Product_Variable $parent */
        $parent = wc_get_product($product->get_parent_id());
        if (empty($product)) {
            return false;
        }

        // the parent product has all variation terms
        $productTerms = getObjectTerms($parent->get_id());
        $variationAttributes = $product->get_variation_attributes(false);
    } else {
        $productTerms = getObjectTerms($product->get_id());
    }

    // if the term taxonomy is an attribute we have to handle that differently
    if (preg_match('/^pa_(.+)$/', $tf->getTaxonomy()) && $product->get_type() === 'variation') {
        if ($skipAttributes) {
            return true;
        }

        $termSlugMap = [];
        foreach ($tf->getTerms() as $tId) {
            $t = get_term($tId, $tf->getTaxonomy());
            if (!empty($t)) {
                $termSlugMap[$t->slug] = $t;
            }
        }

        $attribute = $termSlugMap[$variationAttributes[$tf->getTaxonomy()]] ?? null;

        return ($tf->getCriteria() === ShopRuleTaxFilter::CRITERIA_IN && $attribute !== null)
            || ($tf->getCriteria() === ShopRuleTaxFilter::CRITERIA_NOT_IN && $attribute === null);
    }

    $hasTerm = false;
    foreach ($tf->getTerms() as $tId) {
        if (isset($productTerms[$tf->getTaxonomy()][$tId])) {
            $hasTerm = true;
            break;
        }
    }

    if ($tf->getCriteria() === ShopRuleTaxFilter::CRITERIA_IN) {
        return $hasTerm;
    } elseif ($tf->getCriteria() === ShopRuleTaxFilter::CRITERIA_NOT_IN) {
        return !$hasTerm;
    }

    return false;
}

/**
 * @param ShopRule $shopRule
 * @return array{conditionsMet: bool, itemsToRemove: array}
 */
function getRuleConditionsResult(ShopRule $shopRule): array
{
    $params = $shopRule->getBuyXgetYParam();
    $cart = WC()->cart;
    $cartItems = $cart->get_cart();
    $cartTotal = $params->getCalculatesTotalOnlyBetweenMatchedProducts()
        ? 0.0
        : $cart->get_subtotal() + $cart->get_subtotal_tax();
    // keys of products that are already present into the cart
    $ruleProductsKeys = [];
    $conditionsMet = true;
    $itemConditionsMetCount = 0;
    /** @var array{key: string, product: \WC_Product}|null $matchedItemWithLowestPrice */
    $matchedItemWithLowestPrice = null;

    // creating condition matrix with the following format
    // $conditionMatrix[taxFilterIndex][cartItemKey] = isConditionMet
    $conditionMatrix = [];
    foreach ($shopRule->getTaxFilters() as $i => $tf) {
        foreach ($cartItems as $key => $item) {
            $product = $item['data'] ?? null;
            if (!$product instanceof \WC_Product) {
                continue;
            }

            if (($item['shop_rule_key'] ?? null) === $shopRule->getKey()) {
                $ruleProductsKeys[$product->get_id()] = $key;
                // we should not check conditions for gwt that are not
                // gifted thanks to the param giftLowerPricedMatchedProduct
                if (!$params->getGiftLowerPricedMatchedProduct() &&
                    isset($params->getItemMap()[$product->get_id()])
                ) {
                    continue;
                }
            }

            $conditionMatrix[$i][$key] = doesProductMeetTaxFilterCondition($product, $tf);
        }

        // if atLeastOne is set to true and at list one cart item
        // satisfy the actual tax filter, setting to true the conditionMet
        // variable for all items in the cart
        $sum = array_sum($conditionMatrix[$i] ?? []);
        if ($tf->getAtLeastOne() && $sum > 0) {
            foreach ($conditionMatrix[$i] as &$itemCond) {
                $itemCond = true;
            }
        }
    }

    // transposing the matrix
    // from $conditionMatrix[taxFilterIndex][cartItemKey] = isConditionMet
    // to $itemConditions[cartItemKey][taxFilterIndex] = isConditionMet
    $itemConditions = [];
    foreach ($conditionMatrix as $row) {
        foreach ($row as $key => $cond) {
            $itemConditions[$key][] = $cond;
        }
    }

    foreach ($itemConditions as $key => $conds) {
        $item = $cartItems[$key];
        if (array_sum($conds) < count($conds)) {
            continue;
        }

        $itemConditionsMetCount += $params->getCountMatchedProductsOnce() ? 1 : (int)$item['quantity'];

        $product = $item['data'] ?? null;
        if (!$product instanceof \WC_Product) {
            continue;
        }

        // skipping if item is a gift or gwp
        if (isset($item['shop_rule_key'])) {
            continue;
        }

        if ($params->getCalculatesTotalOnlyBetweenMatchedProducts()) {
            $cartTotal += $item['line_subtotal'] + $item['line_subtotal_tax'];
        }

        if (
            $matchedItemWithLowestPrice === null
            || (float)$product->get_price() < (float)$matchedItemWithLowestPrice['product']->get_price()
        ) {
            $matchedItemWithLowestPrice = [
                'key' => $key,
                'product' => $product,
            ];
        }
    }

    if ($itemConditionsMetCount < $params->getMinMatchedProductCount()) {
        $conditionsMet = false;
    }

    if ($cartTotal < ($params->getMinOrderTotal() ?? 0)) {
        $conditionsMet = false;
    }

    if ($params->getMaxOrderTotal() > 0 && $cartTotal > $params->getMaxOrderTotal()) {
        $conditionsMet = false;
    }

    $allowedRoles = $params->getAllowedRole();
    if (count($allowedRoles) > 0) {
        $conditionsMet = false;
        $user = wp_get_current_user();
        if ($user->ID !== 0) {
            $userData = get_userdata($user->ID);
            if ($userData !== false) {
                foreach ($allowedRoles as $role) {
                    if (in_array($role, $userData->roles)) {
                        $conditionsMet = true;
                        break;
                    }
                }
            }
        }
    }

    $res = [
        'conditionsMet' => $conditionsMet,
        'itemsToRemove' => [],
        'itemsToAdd' => [],
        'itemsToGift' => [],
        'choiceQuantity' => 0,
    ];
    if ($conditionsMet) {
        if ($params->getGiftLowerPricedMatchedProduct()) {
            if ($matchedItemWithLowestPrice !== null) {
                $res['itemsToGift'][] = $matchedItemWithLowestPrice['key'];
            }
        } else {
            if ($params->getAddToCartCriteria() === BuyXGetY::ADD_TO_CART_CRITERIA_AUTO) {
                foreach ($params->getItems() as $i) {
                    if (!isset($ruleProductsKeys[$i->getProductId()])) {
                        $res['itemsToAdd'][] = $i;
                    }
                }
            } elseif ($params->getAddToCartCriteria() === BuyXGetY::ADD_TO_CART_CRITERIA_CHOICE) {
                if (empty($ruleProductsKeys)) {
                    $res['itemsToAdd'] = $params->getItems();
                    if ($params->getChoiceCriteria() === BuyXGetY::CHOICE_CRITERIA_PER_ORDER) {
                        $res['choiceQuantity'] = $params->getMaxNumberOfProductsToChoose();
                    } elseif ($params->getChoiceCriteria() === BuyXGetY::CHOICE_CRITERIA_PER_MATCHED_PRODUCT) {
                        $choiceQuantity = $params->getMaxNumberOfProductsToChoose() * $itemConditionsMetCount;
                        $itemCount = count($params->getItemMap());
                        $res['choiceQuantity'] = $choiceQuantity > $itemCount ? $itemCount : $choiceQuantity;
                    }
                }
            }
        }
    } else {
        $res['itemsToRemove'] = array_values($ruleProductsKeys);
    }

    return $res;
}

function addGwpToCart(ShopRule $r, BuyXGetYItem $i, $extraData = []): ?string
{
    try {
        return WC()->cart->add_to_cart(
            $i->getProductId(),
            $i->getQuantity(),
            0,
            [],
            array_merge($extraData, ['shop_rule_key' => $r->getKey()])
        );
    } catch (\Exception $e) {
        wc_add_notice($e->getMessage(), 'error');
    }

    return null;
}

/**
 * @param ShopRule $r
 * @param array $res
 * @return string[]
 */
function handleGwpFromSubmit(ShopRule $r, array $res): array
{
    $pIds = $_POST[$r->getKey()]['gwp-choice'] ?? [];
    if (count($pIds) > $res['choiceQuantity']) {
        return [];
    }

    $addedItemKeys = [];
    foreach ($pIds as $pId) {
        $i = $r->getBuyXgetYParam()->getItemMap()[$pId] ?? null;
        if ($i === null) {
            continue;
        }

        try {
            $key = addGwpToCart($r, $i);
            if (!empty($key)) {
                $addedItemKeys[] = $key;
            }
        } catch (\Exception $e) {
            wc_add_notice($e->getMessage(), 'error');
        }
    }

    return $addedItemKeys;
}

function removeAllGiftsFromCart(): void
{
    $cart = WC()->cart;
    if ($cart === null) {
        return;
    }

    foreach ($cart->get_cart() as $k => $i) {
        $parent = $i['shop_rule_gift'] ?? null;
        if ($parent !== null) {
            $parentItem = $cart->cart_contents[$parent] ?? null;
            if ($parentItem !== null) {
                $cart->set_quantity($parent, $parentItem['quantity'] + 1);
            } else {
                /** @var \WC_Product $data */
                $data = $i['data'] ?? null;
                if ($data !== null) {
                    $cart->add_to_cart($data->get_id(), 1);
                }
            }

            $cart->remove_cart_item($k);
        }
    }
}

add_action(
    'wp',
    function (): void {
        if (is_admin()) {
            return;
        }

        $cart = WC()->cart;
        removeAllGiftsFromCart();

        $rules = getBuyXGetYRules();
        if (empty($rules)) {
            return;
        }

        // removing expired gwps
        $ruleKeyMap = [];
        foreach ($rules as $r) {
            if (!$r->isActive()) {
                continue;
            }

            $ruleKeyMap[$r->getKey()] = $r->getKey();
        }
        foreach ($cart->get_cart() as $key => $item) {
            $rKey = $item['shop_rule_key'] ?? null;
            if ($rKey !== null && !isset($ruleKeyMap[$rKey])) {
                $cart->remove_cart_item($key);
            }
        }

        /** @var string[] $gwpChoices */
        $gwpChoices = [];
        foreach ($rules as $r) {
            if (!$r->isActive()) {
                continue;
            }

            $params = $r->getBuyXgetYParam();
            $res = getRuleConditionsResult($r);
            if (!$res['conditionsMet']) {
                foreach ($res['itemsToRemove'] as $k) {
                    WC()->cart->remove_cart_item($k);
                }
                continue;
            }

            if ($params->getGiftLowerPricedMatchedProduct()) {
                foreach ($res['itemsToGift'] as $k) {
                    $item = $cart->cart_contents[$k];
                    /** @var \WC_Product $data */
                    $data = $item['data'];
                    $cart->set_quantity($k, $item['quantity'] - 1);
                    addGwpToCart(
                        $r,
                        new BuyXGetYItem($data->get_id(), 1),
                        ['shop_rule_gift' => $k]
                    );
                }
            } else {
                if ($params->getAddToCartCriteria() === BuyXGetY::ADD_TO_CART_CRITERIA_AUTO) {
                    foreach ($res['itemsToAdd'] as $i) {
                        if (apply_filters('shop_rules_add_item_to_cart', true, $i, $r)) {
                            addGwpToCart($r, $i);
                        }
                    }
                } elseif ($params->getAddToCartCriteria() === BuyXGetY::ADD_TO_CART_CRITERIA_CHOICE) {
                    $addedItemKeys = handleGwpFromSubmit($r, $res);
                    if (!empty($addedItemKeys)) {
                        continue;
                    }

                    if (count($res['itemsToAdd']) === 0) {
                        continue;
                    }

                    /** @var \WC_Product[] $gwpProducts */
                    $gwpProducts = [];
                    /** @var BuyXGetYItem $item */
                    foreach ($res['itemsToAdd'] as $item) {
                        $p = wc_get_product($item->getProductId());
                        if (!empty($p)) {
                            $gwpProducts[] = $p;
                        }
                    }

                    ob_start();
                    include getAddonDirectory('shop_rules') . '/templates/gwp-product-choice.php';
                    $gwpChoices[] = [
                        'rule' => $r,
                        'template' => ob_get_clean(),
                    ];
                }
            }
        }

        if (count($gwpChoices) > 0) {
            // cart hooks
            add_action(
                'woocommerce_after_cart_table',
                function () use ($gwpChoices): void {
                    include getAddonDirectory('shop_rules') . '/templates/open-choice-modal.php';
                }
            );

            add_action(
                'woocommerce_before_cart_totals',
                function () use ($gwpChoices): void {
                    include getAddonDirectory('shop_rules') . '/templates/gwp-product-choice-modal.php';
                }
            );

            // checkout hooks
//            add_action(
//                'woocommerce_review_order_after_order_total',
//                function (): void {
//                    include getAddonDirectory('shop_rules') . '/templates/open-choice-modal.php';
//                }
//            );
//
//            add_action(
//                'woocommerce_after_checkout_form',
//                function () use ($gwpChoices): void {
//                    include getAddonDirectory('shop_rules') . '/templates/gwp-product-choice-modal.php';
//                }
//            );
        }
    },
    100
);

add_action(
    'woocommerce_before_calculate_totals',
    function (\WC_Cart $cart) {
        foreach ($cart->cart_contents as &$item) {
            if (!empty($item['shop_rule_key'])) {
                if (empty($item['data']) || !$item['data'] instanceof \WC_Product) {
                    continue;
                }

                $product = &$item['data'];
                $product->set_price(0);
            }
        }
    },
    10
);

add_action('woocommerce_before_mini_cart', function () {
    WC()->cart->calculate_totals();
});

add_action(
    'woocommerce_after_add_to_cart_form',
    function () {
        global $product;

        if (empty($product)) {
            return;
        }

        $rules = getBuyXGetYRules();
        foreach ($rules as $r) {
            if (!$r->isActive()) {
                continue;
            }

            $conditions = [];
            foreach ($r->getTaxFilters() as $tf) {
                $conditions[] = doesProductMeetTaxFilterCondition($product, $tf, true);
            }

            if (array_sum($conditions) === count($conditions)) {
                $params = $r->getBuyXgetYParam();
                $gwpProducts = [];
                foreach ($params->getItems() as $i) {
                    $p = get_post($i->getProductId());
                    if (empty($p)) {
                        continue;
                    }

                    $gwpProducts[] = $p;
                }

                include getAddonDirectory('shop_rules') . '/templates/product-related-gwp.php';
            }
        }
    },
    49
);

// prevent to increase the quantity of a gwp
add_action(
    'woocommerce_after_cart_item_quantity_update',
    function (string $itemKey, int $quantity, int $oldQuantity, \WC_Cart $cart): void {
        if (!isset($cart->cart_contents[$itemKey])) {
            return;
        }

        $item = &$cart->cart_contents[$itemKey];
        if (!empty($item['shop_rule_key'])) {
            $item['quantity'] = $oldQuantity;
        }
    },
    10,
    4
);

add_filter(
    'woocommerce_cart_item_quantity',
    function (string $html, string $itemKey, array $item): string {
        if (empty($item['shop_rule_key'])) {
            return $html;
        }

        return sprintf('%d <input type="hidden" name="cart[%s][qty]" value="1" />', $item['quantity'], $itemKey);
    },
    10,
    3
);
