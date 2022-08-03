<?php

namespace Waboot\addons\packages\shop_rules;

/**
 * @return array
 */
function getCurrentRulesArrayFromDB(): array {
    $currentRules = (new ShopRuleRepository())->getAllShopRules();
    $rulesAsArray = [];
    if(empty($currentRules)){
        return [];
    }
    foreach ($currentRules as $rule){
        if(!$rule instanceof ShopRule){
            continue;
        }
        try{
            $rulesAsArray[] = $rule->toArray();
        }catch (\Exception $e){
            continue;
        }
    }
    return $rulesAsArray;
}

/**
 * @param ShopRule|array $rule
 * @param bool $update
 * @return mixed
 * @uses \Waboot\addons\packages\shop_rules\getNextAvailableRuleId()
 */
function generateRuleIdByRule($rule, bool $update = false){
    $id = getNextAvailableRuleId();
    if($update){
        update_option('wawoo_shop_rules_last_rule_id',$id);
    }
    return $id;
}

/**
 * Every ShopRule must have an integer "id". This functions tries to guess the next available integer based on the
 * fact that updateDBRules() assign an incremental number as id.
 *
 * @return int
 */
function getNextAvailableRuleId(): int {
    $lastId = getLastInsertedRuleId();
    return $lastId + 1;
}

/**
 * @return int
 */
function getLastInsertedRuleId(): int {
    return (int) get_option('wawoo_shop_rules_last_rule_id',0);
}

/**
 * @param int $ruleId
 * @return ShopRule|null
 */
function getRuleById(int $ruleId): ?ShopRule {
    return (new ShopRuleRepository())->getShopRuleById($ruleId);
}

/**
 * @return ShopRule[]
 */
function fetchShopRules(): array {
    return (new ShopRuleRepository())->getAllShopRules();
}

/**
 * Search for json files inside a predefined theme directory and tries to get Rules out of them.
 * In those files terms may be referred by id or by name and products may be referred by id or by SKU.
 *
 * @return ShopRule[]
 */
function getRulesFromTheme(): array {
    $srcDir = get_stylesheet_directory().'/shop_rules';
    $ruleFiles = glob($srcDir.'/*.json');
    if(!\is_array($ruleFiles) || count($ruleFiles) === 0){
        return [];
    }
    $rules = [];
    foreach ($ruleFiles as $ruleFile){
        $ruleFileContent = file_get_contents($ruleFile);
        if(!\is_string($ruleFileContent) || $ruleFileContent === ''){
            continue;
        }
        try{
            $ruleData = jsonDecode($ruleFileContent);
            $rules[] = ShopRule::fromArray($ruleData);
        }catch (\JsonException | \Exception $e){
            continue;
        }
    }
    return $rules;
}

/**
 * Takes an array and tries to generate a ShopRule.
 * Assumes that terms may be referred by id or by name.
 * Assumes that products may be referred by id or by SKU.
 *
 * @param array $ruleData
 * @param string $defaultTimeZoneName
 * @return ShopRule
 * @throws \RuntimeException
 * @throws \Exception
 * @depecated
 */
function generateRuleFromRawArray(array $ruleData, string $defaultTimeZoneName = 'Europe/Rome'): ShopRule {
    return ShopRule::fromArray($ruleData,$defaultTimeZoneName);
}

/**
 * Takes a ShopRule and generate the relative json.
 *
 * @param ShopRule $rule
 * @return string
 * @throws \JsonException
 * @depecated
 */
function generateJsonDataFromRule(ShopRule $rule): string {
    return jsonEncode($rule->toArray());
}

/**
 * Takes a ShopRule and "cast" it to an array.
 *
 * @param ShopRule $rule
 * @return array
 * @depecated
 */
function generateArrayDataFromRule(ShopRule $rule): array {
    return $rule->toArray();
}

/**
 * @param $data
 * @return string
 * @throws \JsonException
 */
function jsonEncode($data): string {
    if(!defined('JSON_THROW_ON_ERROR')) {
        define('JSON_THROW_ON_ERROR',4194304);
    }
    return json_encode($data, JSON_THROW_ON_ERROR);
}

/**
 * @param $json
 * @throws \JsonException
 * @return array
 */
function jsonDecode($json): array {
    if(!defined('JSON_THROW_ON_ERROR')) {
        define('JSON_THROW_ON_ERROR',4194304);
    }
    return \json_decode($json, true,512,JSON_THROW_ON_ERROR);
}

function getObjectTerms(string $objectId): array
{
    global $wpdb;

    $sql = <<<SQL
select t.term_id, tt.term_taxonomy_id, tt.taxonomy, t.name, t.slug, t.term_group, t.term_order, tt.description,
       tt.count, tt.parent
from $wpdb->term_relationships tr
inner join $wpdb->term_taxonomy tt on tt.term_taxonomy_id = tr.term_taxonomy_id
inner join $wpdb->terms t on t.term_id = tt.term_id
where tr.object_id = %s
SQL;

    /** @var \stdClass[] $rawTerms */
    $rawTerms = $wpdb->get_results($wpdb->prepare($sql, $objectId));

    $res = [];
    foreach ($rawTerms as $rt) {
        $term = new \WP_Term($rt);
        $res[$term->taxonomy][$term->term_id] = $term;
    }

    return $res;
}

/**
 * @param ShopRule $rule
 * @return int[]
 */
function findProductIdsByRule(ShopRule $rule): array
{
    $args = [
        'fields' => 'ids',
        'post_type' => 'product',
        'nopaging' => true,
        'post_status' => 'publish',
        'ignore_sticky_posts' => true,
        'suppress_filters' => true,
    ];
    $taxQuery = [
        'relation' => 'AND',
        [
            'taxonomy' => 'product_type',
            'field' => 'slug',
            'terms' => ['simple', 'variable'],
            'operator' => 'IN',
        ],
    ];
    foreach ($rule->getTaxFilters() as $tf) {
        $operator = null;
        switch ($tf->getCriteria()) {
            case ShopRuleTaxFilter::CRITERIA_IN:
                $operator = 'IN';
                break;
            case ShopRuleTaxFilter::CRITERIA_NOT_IN:
                $operator = 'NOT IN';
                break;
        }

        if ($operator === null) {
            continue;
        }

        $taxQuery[] = [
            'taxonomy' => $tf->getTaxonomy(),
            'field' => 'term_id',
            'terms' => $tf->getTerms(),
            'operator' => $operator,
        ];
    }
    $args['tax_query'] = $taxQuery;

    return get_posts($args);
}

/**
 * @param string $role
 * @return bool
 */
function isACustomerRole(string $role): bool {
    $invalidRoles = [
        'administrator',
        'author',
        'contributor',
        'editor',
        'subscriber',
        'webmaster',
        'shop_manager',
        'wpseo_manager',
        'wpseo_editor',
        'revisor'
    ];
    return !\in_array($role,$invalidRoles);
}

function strToBool(string $str): bool
{
    return (bool)filter_var($str, FILTER_VALIDATE_BOOLEAN);
}
