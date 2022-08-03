<?php

namespace Waboot\addons\packages\shop_rules;

use DateTimeInterface;
use Waboot\inc\core\utils\Utilities;
use function Waboot\inc\getAllProductVariationIds;

add_action('wp_ajax_get_rules', __NAMESPACE__.'\\ajaxGetRules');
add_action('wp_ajax_nopriv_get_rules', __NAMESPACE__.'\\ajaxGetRules');
add_action('wp_ajax_get_rule', __NAMESPACE__.'\\ajaxGetRule');
add_action('wp_ajax_nopriv_get_rule', __NAMESPACE__.'\\ajaxGetRule');
add_action('wp_ajax_get_edit_data', __NAMESPACE__.'\\ajaxGetEditData');
add_action('wp_ajax_nopriv_get_edit_data', __NAMESPACE__.'\\ajaxGetEditData');
add_action('wp_ajax_get_products_edit_data', __NAMESPACE__.'\\ajaxGetProductsEditData');
add_action('wp_ajax_nopriv_get_products_edit_data', __NAMESPACE__.'\\ajaxGetProductsEditData');
add_action('wp_ajax_save_rule', __NAMESPACE__.'\\ajaxSaveRule');
add_action('wp_ajax_nopriv_save_rule', __NAMESPACE__.'\\ajaxSaveRule');
add_action('wp_ajax_create_rule', __NAMESPACE__.'\\ajaxCreateRule');
add_action('wp_ajax_nopriv_create_rule', __NAMESPACE__.'\\ajaxCreateRule');
add_action('wp_ajax_delete_rule', __NAMESPACE__.'\\ajaxDeleteRule');
add_action('wp_ajax_nopriv_delete_rule', __NAMESPACE__.'\\ajaxDeleteRule');

/**
 * @return void
 * @throws \Exception
 */
function ajaxGetRules(): void {
    try{
        $rules = getCurrentRulesArrayFromDB();
        wp_send_json_success($rules);
    }catch (\JsonException | \Exception | \Throwable $e){
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}

/**
 * @return void
 */
function ajaxGetRule(): void {
    try{
        $ruleId = $_POST['rule_id'] ?? null;
        if(!$ruleId){
            throw new \Exception('Invalid rule_id');
        }
        $rule = getRuleById((int) $ruleId);
        if(!$rule){
            throw new \Exception('Rule with specified rule_id not found');
        }
        $ruleArray = $rule->toArray();
        wp_send_json_success($ruleArray);
    }catch (\JsonException | \Exception $e){
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}

/**
 * @return void
 */
function ajaxGetEditData(): void {
    $excludedTaxonomies = ['product_type','product_visibility','product_shipping_class'];
    $taxonomiesAndTerms = Utilities::getObjectTaxonomiesAndTerms('product', static function(\WP_Taxonomy $taxObj) use($excludedTaxonomies){
        //return !\in_array($taxObj->name,$excludedTaxonomies,true) && strpos($taxObj->name,'pa_') === false;
        return !\in_array($taxObj->name,$excludedTaxonomies,true);
    });
    $terms = [];
    $taxonomies = [];
    foreach ($taxonomiesAndTerms as $taxonomyData){
        $terms[$taxonomyData['taxonomy']->name] = array_values($taxonomyData['terms']);
        $taxonomies[$taxonomyData['taxonomy']->name] = $taxonomyData['taxonomy'];
    }
    $rolesInstance = wp_roles();
    $customersRoles = [];
    if($rolesInstance instanceof \WP_Roles){
        foreach ($rolesInstance->roles as $roleSlug => $roleData){
            if(!isACustomerRole($roleSlug)){
                continue;
            }
            $customersRoles[] = [
                'slug' => $roleSlug,
                'label' => $roleData['name']
            ];
        }
    }
    $result = [
        'taxonomies' => $taxonomies,
        'terms' => $terms,
        'products' => [],
        'user_roles' => $customersRoles,
        'shop_rule_types' => [
            [
                'slug' => 'buy-x-get-y',
                'label' => 'Buy X Get Y'
            ],
            [
                'slug' => 'join-taxonomy',
                'label' => 'Join Taxonomy'
            ],
            [
                'slug' => 'sale',
                'label' => 'Sale'
            ],
            [
                'slug' => 'cart-adjustment',
                'label' => 'Cart Adjustment',
            ]
        ],
        'add_to_cart_criteria' => [
            [
                'slug' => 'choice',
                'label' => 'Choice'
            ],
            [
                'slug' => 'auto',
                'label' => 'Auto'
            ]
        ],
        'product_page_message_layouts' => [
            [
                'slug' => 'list',
                'label' => 'List'
            ],
            [
                'slug' => 'message',
                'label' => 'Message'
            ],
            [
                'slug' => 'hidden',
                'label' => 'Hidden'
            ]
        ],
        'sale_criteria' => [
            [
                'slug' => 'cumulative',
                'label' => 'Cumulative'
            ],
            [
                'slug' => 'replacement',
                'label' => 'Replacement'
            ]
        ],
        'sale_type' => [
            [
                'slug' => 'percentage',
                'label' => 'Percentage'
            ],
            [
                'slug' => 'flat',
                'label' => 'Flat'
            ]
        ],
        'choice_criteria' => [
            [
                'slug' => 'per-order',
                'label' => 'Per order'
            ],
            [
                'slug' => 'per-matched-product',
                'label' => 'Per matched product'
            ]
        ],
        'discount_type' => [
            [
                'slug' => 'flat',
                'label' => 'Flat',
            ],
            [
                'slug' => 'percentage',
                'label' => 'Percentage',
            ],
        ],
    ];
    wp_send_json_success($result);
}

/**
 * @return void
 */
function ajaxGetProductsEditData(): void {
    $products = [];
    $availableProductsCache = get_transient('wawoo_shop_rules_available_products');
    if(\is_array($availableProductsCache) && count($availableProductsCache) > 0){
        $products = $availableProductsCache;
    }else{
        $productsPostsQuery = new \WP_Query([
            'post_type' => ['product'],
            'posts_per_page' => -1,
            'fields' => 'ids'
        ]);
        foreach ($productsPostsQuery->get_posts() as $postId){
            $type = \WC_Product_Factory::get_product_type($postId);
            if($type === 'variable'){
                $sku = get_post_meta($postId,'_sku',true);
                if($sku !== ''){
                    $products[] = [
                        'id' => (int) $postId,
                        'sku' => $sku,
                        'type' => $type
                    ];
                }
                $variationIds = getAllProductVariationIds($postId);
                if(count($variationIds) > 0){
                    foreach ($variationIds as $variationId){
                        $sku = get_post_meta($variationId,'_sku',true);
                        if($sku !== ''){
                            $products[] = [
                                'id' => (int) $variationId,
                                'sku' => $sku,
                                'type' => 'variation',
                                'parent' => $postId
                            ];
                        }
                    }
                }
            }else{
                $sku = get_post_meta($postId,'_sku',true);
                if($sku !== ''){
                    $products[] = [
                        'id' => (int) $postId,
                        'sku' => $sku,
                        'type' => $type
                    ];
                }
            }
        }
        set_transient('wawoo_shop_rules_available_products',$products,21600); //6 hours
    }
    wp_send_json_success($products);
}

/**
 * @return void
 */
function ajaxCreateRule(): void {
    $ruleData = $_POST['form_data'] ?? null;
    if(!$ruleData){
        wp_send_json_error();
    }
    try{
        $sr = new ShopRuleRepository();
        $newRule = $sr->createShopRule($ruleData);
        wp_send_json_success(['rule_id' => $newRule->getId()]);
    }catch (\Exception | \Throwable $e){
        wp_send_json_error(['error' => $e->getMessage(), 'code' => $e->getCode()]);
    }
    wp_send_json_error(['error' => 'Rule not created', 'code' => 'shop_rule_not_created']);
}

/**
 * @return void
 */
function ajaxDeleteRule(): void {
    $ruleId = $_POST['rule_id'] ?? null;
    if(!$ruleId){
        wp_send_json_error(['error' => 'Rule id not provided', 'code' => 'shop_rule_not_deleted']);
    }
    try{
        $sr = new ShopRuleRepository();
        $deleted = $sr->deleteRule((int) $ruleId);
        if($deleted){
            wp_send_json_success();
        }
        throw new \RuntimeException('Rule not deleted');
    }catch (\Exception | \Throwable $e){
        wp_send_json_error(['error' => $e->getMessage(), 'code' => $e->getCode()]);
    }
    wp_send_json_error(['error' => 'Rule not deleted', 'code' => 'shop_rule_not_deleted']);
}

/**
 * @return void
 */
function ajaxSaveRule(): void {
    $ruleId = $_POST['rule_id'] ?? null;
    if(!$ruleId){
        wp_send_json_error();
    }
    $ruleData = $_POST['form_data'] ?? null;
    if(!$ruleData){
        wp_send_json_error();
    }
    try{
        $sr = new ShopRuleRepository();
        $sr->updateShopRule($ruleId,$ruleData);
    }catch (\Exception | \Throwable $e){
        wp_send_json_error(['error' => $e->getMessage(), 'code' => $e->getCode()]);
    }
    wp_send_json_success();
}