<?php

namespace Waboot\addons\packages\shop_rules\cli;

use Waboot\addons\packages\shop_rules\ShopRule;
use Waboot\inc\core\cli\AbstractCommand;

use function Waboot\addons\packages\shop_rules\fetchShopRules;
use function Waboot\addons\packages\shop_rules\findProductIdsByRule;

class JoinTaxonomy extends AbstractCommand
{
    const JOINED_TAXONOMIES_META = '_shop_rules_joined_taxonomies';

    protected $logDirName = 'join-taxonomies';
    protected $logFileName = 'join-taxonomies';

    public function __invoke($args, $assoc_args): void
    {
        global $wpdb;

        if (isset($assoc_args['reset']) && boolval($assoc_args['reset']) === true) {
            $meta = self::JOINED_TAXONOMIES_META;
            $sql = <<<SQL
select * from $wpdb->postmeta pm where pm.meta_key = '$meta'
SQL;
            $res = $wpdb->get_results($sql, ARRAY_A);
            foreach ($res as $m) {
                $metaId = $m['meta_id'];
                $postId = $m['post_id'];
                $ttIds = unserialize($m['meta_value']);
                if (empty($ttIds) || !is_array($ttIds)) {
                    continue;
                }

                foreach ($ttIds as $ttId) {
                    $wpdb->delete(
                        $wpdb->term_relationships,
                        [
                            'object_id' => $postId,
                            'term_taxonomy_id' => $ttId,
                        ],
                        ['%d', '%d']
                    );
                }

                $wpdb->delete($wpdb->postmeta, ['meta_id' => $metaId], ['%d']);
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
                    return $r->getType() === ShopRule::TYPE_JOIN_TAXONOMY;
                }
            )
        );

        $ruleCount = count($rules);
        foreach ($rules as $i => $r) {
            $this->log(
                sprintf(
                    'Running rule %d/%d',
                    $i + 1,
                    $ruleCount
                )
            );

            if (!$r->isActive()) {
                $this->log("The rule does not match the current date");
                continue;
            }

            $joinTax = $r->getJoinTaxParam();
            $term = get_term_by('id', $joinTax->getTermId(), $joinTax->getTaxonomy());
            if (empty($term)) {
                $this->log('Term not found. Skipping rule');
                continue;
            }

            $productIds = findProductIdsByRule($r);
            $productCount = count($productIds);
            $this->log("Found $productCount products");
            foreach ($productIds as $ii => $pId) {
                $this->log(
                    sprintf(
                        'Updating product %d/%d',
                        $ii + 1,
                        $productCount
                    )
                );
                $product = wc_get_product($pId);
                if (empty($product)) {
                    $this->log("Product #$pId not found");
                    continue;
                }

                wp_set_post_terms(
                    $product->get_id(),
                    $term->term_id,
                    'selection_taxonomy',
                    true
                );

                $joinedTaxonomies = $product->get_meta(self::JOINED_TAXONOMIES_META);
                if (empty($joinedTaxonomies) || !is_array($joinedTaxonomies)) {
                    $joinedTaxonomies = [$term->term_taxonomy_id];
                } elseif (!in_array($term->term_taxonomy_id, $joinedTaxonomies)) {
                    $joinedTaxonomies[] = $term->term_taxonomy_id;
                }

                $product->update_meta_data(self::JOINED_TAXONOMIES_META, $joinedTaxonomies);
                $product->save_meta_data();
            }
        }

        $this->logger->notice("Done");
    }
}
