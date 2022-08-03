<?php

namespace Waboot\addons\packages\shop_rules\cli;

use Waboot\addons\packages\shop_rules\rule_params\Sale;
use Waboot\addons\packages\shop_rules\ShopRule;
use Waboot\addons\packages\shop_rules\ShopRuleTaxFilter;
use Waboot\inc\core\cli\AbstractCommand;

use function Waboot\addons\packages\shop_rules\fetchShopRules;
use function Waboot\addons\packages\shop_rules\findProductIdsByRule;

class CalculateSales extends AbstractCommand
{
    const ON_SALE_META = '_shop_rules_on_sale';

    protected $logDirName = 'calculate-sales';
    protected $logFileName = 'calculate-sales';

    /** @var array<int, \WC_Product> $prodCache */
    private $prodCache = [];

    public function __invoke($args, $assoc_args): void
    {
        global $wpdb;

        if (isset($assoc_args['reset']) && boolval($assoc_args['reset']) === true) {
            $meta = self::ON_SALE_META;
            $sql = <<<SQL
select * from $wpdb->postmeta pm where pm.meta_key = '$meta'
SQL;
            $res = $wpdb->get_results($sql, ARRAY_A);
            foreach ($res as $m) {
                $product = $this->getProduct($m['post_id']);
                if ($product === null) {
                    continue;
                }

                $this->log(sprintf('Reset sale price for product #%s', $product->get_id()));

                $shopRuleSaleMeta = maybe_unserialize($m['meta_value']);
                $oldSalePrice = $shopRuleSaleMeta['old_sale_price'] ?? null;
                if ($oldSalePrice === null) {
                    $product->set_sale_price('');
                    $product->set_price($product->get_regular_price());
                } else {
                    $product->set_sale_price($oldSalePrice);
                    $product->set_price($oldSalePrice);
                }
                $product->delete_meta_data_by_mid($m['meta_id']);
                $product->save();
            }
        }

        $rules = fetchShopRules();
        if (empty($rules)) {
            $this->error('No rules found');
        }

        /** @var ShopRule[] $rules */
        $rules = array_values(
            array_filter(
                $rules,
                function (ShopRule $r): bool {
                    return $r->getType() === ShopRule::TYPE_SALE;
                }
            )
        );

        usort($rules, function (ShopRule $a, ShopRule $b): int {
            return $a->getOrder() - $b->getOrder();
        });

        $variableToSync = [];
        $ruleCount = count($rules);
        foreach ($rules as $i => $r) {
            $this->log(
                sprintf(
                    'Running rule `%s` %d/%d',
                    $r->getName(),
                    $i + 1,
                    $ruleCount
                )
            );

            if (!$r->isActive()) {
                $this->log("The rule does not match the current date");
                continue;
            }

            $sale = $r->getSaleParam();
            $productIds = findProductIdsByRule($r);
            $productCount = count($productIds);
            $this->log("Found $productCount products");
            foreach ($productIds as $ii => $pId) {
                $this->log(
                    sprintf(
                        'Updating product #%s %d/%d',
                        $pId,
                        $ii + 1,
                        $productCount
                    )
                );

                $product = $this->getProduct($pId);
                if (empty($product)) {
                    $this->log("Product #$pId not found");
                    continue;
                }

                if ($product->get_type() === 'simple') {
                    /** @var \WC_Product_Simple $product */
                    $this->calculateSalePrice($product, $sale);
                } elseif ($product->get_type() === 'variable') {
                    /** @var \WC_Product_Variable $product */
                    $variations = $product->get_available_variations('object');
                    foreach ($variations as $v) {
                        $this->log(sprintf('- Updating variation #%s', $v->get_id()));
                        $conditionsMet = true;
                        foreach ($r->getTaxFilters() as $tf) {
                            if (strpos($tf->getTaxonomy(), 'pa_') === false) {
                                continue;
                            }

                            $terms = [];
                            foreach ($tf->getTerms() as $tId) {
                                $t = get_term($tId, $tf->getTaxonomy());
                                if (!empty($t)) {
                                    $terms[] = $t->slug;
                                }
                            }

                            $hasAttribute = in_array(
                                $v->get_variation_attributes()['attribute_' . $tf->getTaxonomy()],
                                $terms
                            );
                            if (
                                ($tf->getCriteria() === ShopRuleTaxFilter::CRITERIA_NOT_IN && $hasAttribute)
                                || ($tf->getCriteria() === ShopRuleTaxFilter::CRITERIA_IN && !$hasAttribute)
                            ) {
                                $conditionsMet = false;
                                break;
                            }
                        }

                        if ($conditionsMet) {
                            $this->calculateSalePrice($v, $sale);
                        }
                    }
                    $variableToSync[$product->get_id()] = $product->get_id();
                } else {
                    $this->log(sprintf('Invalid product type %s', $product->get_type()));
                }
            }
        }

        $variableCount = count($variableToSync);
        foreach (array_values($variableToSync) as $i => $vId) {
            $this->log(sprintf('Syncing product #%s %d/%d', $vId, $i + 1, $variableCount));
            \WC_Product_Variable::sync($vId);
        }

        $this->logger->notice("Done");
    }

    private function calculateSalePrice(\WC_Product $product, Sale $sale): void
    {
        /** @var float|null $prevSalePrice */
        $prevSalePrice = null;
        if ($product->is_on_sale()) {
            $prevSalePrice = is_numeric($product->get_sale_price()) ? $product->get_sale_price() : null;
        }

        if ($sale->getSaleCriteria() === Sale::SALE_CRITERIA_REPLACEMENT) {
            $prevPrice = (float)$product->get_regular_price();
        } elseif ($sale->getSaleCriteria() === Sale::SALE_CRITERIA_CUMULATIVE) {
            $prevPrice = $prevSalePrice === null ? (float)$product->get_regular_price() : $prevSalePrice;
        } else {
            $this->log(sprintf('ERROR: `%s` is not a valid sale criteria', $sale->getSaleCriteria()));
            return;
        }

        if ($sale->getSaleType() === Sale::SALE_TYPE_PERCENTAGE) {
            $salePrice = $prevPrice * $sale->getPercentageMultiplier();
        } elseif ($sale->getSaleType() === Sale::SALE_TYPE_FLAT) {
            $salePrice = $prevPrice - $sale->getSaleValue();
            $salePrice = $salePrice < 0 ? 0 : $salePrice;
        } else {
            $this->log("Invalid sale type. Skipping");
            return;
        }

        $product->set_sale_price($salePrice);
        $product->set_price($salePrice);

        $shopRuleSaleMeta = $product->get_meta(self::ON_SALE_META);
        if (empty($shopRuleSaleMeta)) {
            $oldSalePrice = $prevSalePrice;
        } else {
            /** @var float|null $oldSalePrice */
            $oldSalePrice = $shopRuleSaleMeta['old_sale_price'] ?? null;
        }

        $product->update_meta_data(self::ON_SALE_META, ['old_sale_price' => $oldSalePrice]);
        $product->save();
    }

    private function getProduct(int $productId): ?\WC_Product
    {
        $prod = $this->prodCache[$productId] ?? null;
        if ($prod === null) {
            $prod = wc_get_product($productId);
            if (empty($prod)) {
                return null;
            }

            $this->prodCache[$prod->get_id()] = $prod;
        }

        return $prod;
    }
}
