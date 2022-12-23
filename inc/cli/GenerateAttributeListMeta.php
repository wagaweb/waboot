<?php

namespace Waboot\inc\cli;

use Waboot\inc\core\cli\AbstractCommand;

class GenerateAttributeListMeta extends AbstractCommand
{
    /** @var array<string, array<int, \WP_Term>> */
    private $taxCache = [];

    /**
     * @return array
     */
    public static function getCommandDescription(): array
    {
        //@see: https://make.wordpress.org/cli/handbook/guides/commands-cookbook/#wp_cliadd_commands-third-args-parameter
        return [
            'shortdesc' => 'Generate `_attribute_list` metadata for each variable product',
            'synopsis' => [
                [
                    'type' => 'positional',
                    'name' => 'ids',
                    'description' => 'The ID of the product to process',
                    'optional' => true,
                    'repeating' => true,
                ],
            ],
        ];
    }

    public function __invoke(array $args, array $assoc_args)
    {
        parent::__invoke($args, $assoc_args);

        $query = ['limit' => -1, 'type' => 'variable'];
        if (!empty($args)) {
            $query['include'] = $args;
        }

        $products = wc_get_products($query);
        $count = count($products);
        $this->log(sprintf('Found %d products', $count));

        /** @var \WC_Product_Variable $p */
        foreach ($products as $i => $p) {
            $this->log(sprintf('%d/%d: Processing product #%d', $i + 1, $count, $p->get_id()));
            /** @var array{
             *     name: string,
             *     value: string,
             *     position: int,
             *     is_visible: int,
             *     is_variation: int,
             *     is_taxonomy: int
             * }[] $varAttr
             */
            $varAttrs = get_post_meta($p->get_id(), '_product_attributes', true);
            $varAttrs = array_filter($varAttrs, function ($a) {
                return $a['is_variation'] ?? 0;
            });
            $attrCount = count($varAttrs);
            if ($attrCount != 1) {
                $this->log(sprintf('Found %d variation attributes. Resetting metadata', $attrCount));
                $p->delete_meta_data('_attribute_list');
                $p->save_meta_data();
                continue;
            }
            $varAttr = array_values($varAttrs)[0];
            $attrName = $varAttr['name'];
            $variations = $this->getProductVariations($p->get_id());
            if (count($variations) === 0) {
                $this->log(sprintf('No variation found for product #%d', $p->get_id()));
                continue;
            }
            $wvsMeta = $p->get_meta('_wvs_product_attributes');

            $termValues = [];
            $attrType = 'select';
            if (empty($wvsMeta)) {
                if ($varAttr['is_taxonomy']) {
                    foreach ($this->getTaxTerms($attrName) as $t) {
                        $termValues[$t->slug] = $t->name;
                    }
                } else {
                    foreach (explode(' | ', $varAttr['value']) as $v) {
                        $termValues[$v] = $v;
                    }
                }
            } else {
                $wvsAttr = $wvsMeta[$attrName];
                $attrType = $wvsAttr['type'];
                $taxTerms = $this->getTaxTerms($attrName);
                foreach ($wvsAttr['terms'] as $tId => $d) {
                    $t = $taxTerms[$tId] ?? null;
                    if ($t === null) {
                        continue;
                    }

                    switch ($attrType) {
                        case 'color':
                            $termValues[$t->slug] = get_term_meta($t->term_id, 'product_attribute_color', true);
                            break;
                        case 'image':
                            $attachmentId = get_term_meta($t->term_id, 'product_attribute_image', true);
                            if (!empty($attachmentId) && function_exists('\acf_get_attachment')) {
                                $termValues[$t->slug] = acf_get_attachment($attachmentId);
                            }
                            break;
                        case 'select':
                            $termValues[$t->slug] = $t->name;
                            break;
                    }
                }
            }

            $result = [
                'attribute' => $attrName,
                'type' => $attrType,
                'variations' => [],
            ];
            foreach ($variations as $v) {
                $result['variations'][] = [
                    'variation' => $v->get_id(),
                    'sku' => $v->get_sku(),
                    'stockStatus' => $v->get_stock_status(),
                    'data' => $termValues[get_post_meta($v->get_id(), 'attribute_' . $attrName, true)] ?? null,
                ];
            }

            $p->update_meta_data('_attribute_list', wp_json_encode($result));
            $p->save_meta_data();
            $this->log(sprintf('Updated product #%d', $p->get_id()));
        }
    }

    /**
     * @param int $id
     * @return array<int, \WC_Product_Variation>
     */
    public function getProductVariations(int $id): array
    {
        global $wpdb;

        $sql = <<<SQL
select ID from $wpdb->posts where post_parent = %d and post_type = 'product_variation'
SQL;
        $res = $wpdb->get_col($wpdb->prepare($sql, $id));

        $prods = [];
        foreach ($res as $v) {
            $v = wc_get_product($v);
            if (empty($v)) {
                continue;
            }

            $prods[$v->get_id()] = $v;
        }

        return $prods;
    }

    public function getTaxTerms(string $tax): array
    {
        $terms = $this->taxCache[$tax] ?? null;
        if ($terms === null) {
            $terms = [];
            $res = get_terms(['taxonomy' => $tax, 'hide_empty' => false]);
            foreach ($res as $t) {
                $terms[$t->term_id] = $t;
            }

            $this->taxCache[$tax] = $terms;
        }

        return $terms;
    }
}
