<?php

namespace Waboot\addons\packages\catalog\cli;

use Waboot\inc\core\cli\AbstractCommand;

use function Waboot\addons\packages\catalog\updateCatalogProductMetadata;

class GenCatalogMeta extends AbstractCommand
{
    public function __invoke(array $args, array $assoc_args)
    {
        parent::__invoke($args, $assoc_args);

        $query = ['limit' => -1];
        if (empty($args)) {
            $query['type'] = 'variable';
        } else {
            $query['include'] = $args;
        }

        $products = wc_get_products($query);
        $count = count($products);
        $this->log(sprintf('Found %d products', $count));

        /** @var \WC_Product $p */
        foreach ($products as $i => $p) {
            if ($p->get_type() === 'variation') {
                $p = wc_get_product($p->get_parent_id());
                if (empty($p)) {
                    continue;
                }
            }

            $this->log(sprintf('%d/%d: Processing product #%d', $i + 1, $count, $p->get_id()));
            updateCatalogProductMetadata($p);
            $this->log(sprintf('Updated product #%d', $p->get_id()));
        }
    }
}
